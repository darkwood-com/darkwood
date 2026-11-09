<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Training\SupervisedTrainingService;
use App\Application\Training\UnsupervisedTrainingService;
use App\Domain\LambdaLanguage\ProgramValidationException;
use App\Infrastructure\Navi\LifeRunLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:benchmark:run',
    description: 'Run benchmark for supervised or unsupervised training.',
)]
final class BenchmarkRunCommand extends Command
{
    public function __construct(
        private readonly SupervisedTrainingService $supervised,
        private readonly UnsupervisedTrainingService $unsupervised,
        private readonly LifeRunLogger $runLogger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'supervised|unsupervised', 'unsupervised')
            ->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Dataset for supervised mode.', 'block')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Grid size for unsupervised mode.', '16')
            ->addOption('population', null, InputOption::VALUE_REQUIRED, 'Population size.', '40')
            ->addOption('generations', null, InputOption::VALUE_REQUIRED, 'Generation count.', '25')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'Deterministic seed.', '42')
            ->addOption('max-frames', null, InputOption::VALUE_REQUIRED, 'Frame budget.', '30')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Output benchmark JSON path.', 'var/benchmarks/result.json');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runId = 'benchmark_' . bin2hex(random_bytes(6));

        try {
            $mode = (string) $input->getOption('mode');
            $population = $this->readPositiveInt((string) $input->getOption('population'), 'population');
            $generations = $this->readPositiveInt((string) $input->getOption('generations'), 'generations');
            $seed = $this->readPositiveInt((string) $input->getOption('seed'), 'seed');
            $maxFrames = $this->readPositiveInt((string) $input->getOption('max-frames'), 'max-frames');
            $size = $this->readPositiveInt((string) $input->getOption('size'), 'size');
            $datasetName = (string) $input->getOption('dataset');

            $start = microtime(true);
            $startMemory = memory_get_usage(true);
            [$chunksA, $meta] = $this->runTraining($mode, $datasetName, $size, $population, $generations, $seed, $maxFrames);
            [$chunksB] = $this->runTraining($mode, $datasetName, $size, $population, $generations, $seed, $maxFrames);
            $durationMs = (int) round((microtime(true) - $start) * 1000);
            $peakMemoryMb = round((memory_get_peak_usage(true) - $startMemory) / 1024 / 1024, 2);

            $hashA = hash('sha256', json_encode($chunksA, JSON_THROW_ON_ERROR));
            $hashB = hash('sha256', json_encode($chunksB, JSON_THROW_ON_ERROR));
            $last = $chunksA[array_key_last($chunksA)] ?? [];

            $result = [
                'schema_version' => 1,
                'case' => $meta['case'],
                'mode' => $mode,
                'dataset' => $mode === 'supervised' ? $datasetName : null,
                'size' => $mode === 'unsupervised' ? $size : null,
                'seed' => $seed,
                'hyperparameters' => [
                    'population' => $population,
                    'generations' => $generations,
                    'max_frames' => $maxFrames,
                ],
                'php_version' => \PHP_VERSION,
                'duration_ms' => $durationMs,
                'peak_memory_mb' => $peakMemoryMb,
                'final_fitness' => $last['best_fitness'] ?? null,
                'final_average_fitness' => $last['average_fitness'] ?? null,
                'generations' => $generations,
                'program_size' => isset($last['best_program']['nodes']) && \is_array($last['best_program']['nodes']) ? \count($last['best_program']['nodes']) : null,
                'sequence_hash' => $hashA,
                'reproducible' => $hashA === $hashB,
                'reproducibility' => [
                    'method' => 'double_run_identical_params',
                    'match' => $hashA === $hashB,
                    'first_hash' => $hashA,
                    'second_hash' => $hashB,
                ],
                'stability' => $this->stability($chunksA),
                'diversity' => null,
                'visual_perplexity' => null,
            ];

            $outputPath = $this->resolveOutputPath((string) $input->getOption('output'));
            $this->writeJson($outputPath, $result);
            $output->writeln(json_encode($result, JSON_THROW_ON_ERROR));

            $this->runLogger->logBenchmark($runId, $result);
        } catch (\Throwable $exception) {
            $this->runLogger->logBenchmark($runId, ['event' => 'benchmark_failed', 'error' => $exception->getMessage()]);
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array{0:list<array<string,mixed>>,1:array{case:string}}
     */
    private function runTraining(string $mode, string $datasetName, int $size, int $population, int $generations, int $seed, int $maxFrames): array
    {
        if ($mode === 'supervised') {
            $dataset = $this->loadDataset($datasetName);
            $chunks = iterator_to_array($this->supervised->train($dataset, $datasetName, $population, $generations, $seed, $maxFrames), false);

            return [$chunks, ['case' => 'supervised_' . $datasetName]];
        }

        if ($mode !== 'unsupervised') {
            throw new ProgramValidationException('Mode must be supervised or unsupervised.');
        }

        $chunks = iterator_to_array($this->unsupervised->train($size, $population, $generations, $seed, $maxFrames), false);

        return [$chunks, ['case' => 'unsupervised_' . $size]];
    }

    /**
     * @param list<array<string,mixed>> $chunks
     *
     * @return array{window:int,best_fitness_range:float}|null
     */
    private function stability(array $chunks): ?array
    {
        $fitnessValues = [];
        foreach (\array_slice($chunks, -5) as $chunk) {
            $fitness = $chunk['best_fitness'] ?? null;
            if (\is_int($fitness) || \is_float($fitness)) {
                $fitnessValues[] = (float) $fitness;
            }
        }

        if ($fitnessValues === []) {
            return null;
        }

        return [
            'window' => \count($fitnessValues),
            'best_fitness_range' => round(max($fitnessValues) - min($fitnessValues), 6),
        ];
    }

    /**
     * @return array<string,mixed>
     */
    private function loadDataset(string $name): array
    {
        $path = sprintf('%s/data/patterns/%s.json', $this->projectDir, $name);
        if (!is_file($path)) {
            throw new ProgramValidationException(sprintf('Dataset "%s" not found.', $name));
        }

        $raw = file_get_contents($path);
        if (!\is_string($raw)) {
            throw new ProgramValidationException(sprintf('Unable to read dataset "%s".', $name));
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new ProgramValidationException('Dataset must be a JSON object.');
        }

        return $decoded;
    }

    private function resolveOutputPath(string $raw): string
    {
        $path = str_starts_with($raw, '/') ? $raw : $this->projectDir . '/' . ltrim($raw, '/');
        $dir = \dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Unable to create benchmark output directory "%s".', $dir));
        }

        return $path;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function writeJson(string $path, array $payload): void
    {
        $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
        if (file_put_contents($path, $encoded) === false) {
            throw new \RuntimeException(sprintf('Unable to write benchmark output to "%s".', $path));
        }
    }

    private function readPositiveInt(string $raw, string $name): int
    {
        if (!ctype_digit($raw)) {
            throw new ProgramValidationException(sprintf('Option --%s must be a positive integer.', $name));
        }

        $value = (int) $raw;
        if ($value <= 0) {
            throw new ProgramValidationException(sprintf('Option --%s must be greater than 0.', $name));
        }

        return $value;
    }
}
