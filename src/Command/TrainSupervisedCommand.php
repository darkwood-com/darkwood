<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Training\SupervisedTrainingService;
use App\Domain\LambdaLanguage\ProgramValidationException;
use App\Infrastructure\Navi\LifeRunLogger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:train:supervised',
    description: 'Run supervised evolution against a dataset and stream generation chunks.',
)]
final class TrainSupervisedCommand extends Command
{
    public function __construct(
        private readonly SupervisedTrainingService $training,
        private readonly LifeRunLogger $runLogger,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dataset', null, InputOption::VALUE_REQUIRED, 'Dataset name (glider|blinker|block).', 'glider')
            ->addOption('population', null, InputOption::VALUE_REQUIRED, 'Population size.', '40')
            ->addOption('generations', null, InputOption::VALUE_REQUIRED, 'Generation count.', '25')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'Deterministic random seed.', '42')
            ->addOption('max-frames', null, InputOption::VALUE_REQUIRED, 'Frame budget per execution.', '30')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Optional NDJSON output file path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $runId = 'supervised_' . bin2hex(random_bytes(6));

        try {
            $datasetName = (string) $input->getOption('dataset');
            $dataset = $this->loadDataset($datasetName);
            $population = $this->readPositiveInt((string) $input->getOption('population'), 'population');
            $generations = $this->readPositiveInt((string) $input->getOption('generations'), 'generations');
            $seed = $this->readPositiveInt((string) $input->getOption('seed'), 'seed');
            $maxFrames = $this->readPositiveInt((string) $input->getOption('max-frames'), 'max-frames');
            $outputPath = $this->readOptionalOutputPath($input->getOption('output'));

            $this->runLogger->log($runId, 'training_started', [
                'mode' => 'supervised',
                'dataset' => $datasetName,
                'population' => $population,
                'generations' => $generations,
                'seed' => $seed,
            ]);

            $chunks = [];
            foreach ($this->training->train($dataset, $datasetName, $population, $generations, $seed, $maxFrames) as $chunk) {
                $chunks[] = $chunk;
                $output->writeln(json_encode($chunk, JSON_THROW_ON_ERROR));
                $this->runLogger->log($runId, 'generation_completed', [
                    'mode' => 'supervised',
                    'dataset' => $datasetName,
                    'generation' => $chunk['generation'] ?? null,
                    'best_fitness' => $chunk['best_fitness'] ?? null,
                    'average_fitness' => $chunk['average_fitness'] ?? null,
                ]);
            }

            if ($outputPath !== null) {
                $this->writeOutputs($outputPath, $chunks);
            }

            $this->runLogger->log($runId, 'training_completed', [
                'mode' => 'supervised',
                'dataset' => $datasetName,
                'output' => $outputPath,
                'chunks' => \count($chunks),
            ]);
        } catch (\Throwable $exception) {
            $this->runLogger->log($runId, 'training_failed', [
                'mode' => 'supervised',
                'error' => $exception->getMessage(),
            ]);
            $io->error($exception->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string,mixed>
     */
    private function loadDataset(string $name): array
    {
        $path = sprintf('%s/data/patterns/%s.json', $this->projectDir, $name);
        if (!is_file($path)) {
            throw new ProgramValidationException(sprintf('Dataset "%s" not found at %s.', $name, $path));
        }

        $raw = file_get_contents($path);
        if (!\is_string($raw) || $raw === '') {
            throw new ProgramValidationException(sprintf('Dataset "%s" is empty.', $name));
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new ProgramValidationException('Dataset JSON must decode to an object.');
        }

        return $decoded;
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

    private function readOptionalOutputPath(mixed $raw): ?string
    {
        if (!\is_string($raw) || trim($raw) === '') {
            return null;
        }
        return $raw;
    }

    /**
     * @param list<array<string,mixed>> $chunks
     */
    private function writeOutputs(string $outputPath, array $chunks): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Unable to create output directory "%s".', $directory));
        }

        $ndjson = implode("\n", array_map(static fn (array $chunk): string => json_encode($chunk, JSON_THROW_ON_ERROR), $chunks));
        if ($ndjson !== '') {
            $ndjson .= "\n";
        }
        if (file_put_contents($outputPath, $ndjson) === false) {
            throw new \RuntimeException(sprintf('Unable to write NDJSON output to "%s".', $outputPath));
        }

        $lastChunk = $chunks[array_key_last($chunks)] ?? null;
        if (!\is_array($lastChunk)) {
            return;
        }

        $summaryPath = preg_replace('/\.ndjson$/', '', $outputPath) . '.summary.json';
        $summary = [
            'dataset' => $lastChunk['dataset'] ?? null,
            'final_generation' => $lastChunk['generation'] ?? null,
            'best_fitness' => $lastChunk['best_fitness'] ?? null,
            'average_fitness' => $lastChunk['average_fitness'] ?? null,
            'seed' => $lastChunk['seed'] ?? null,
            'population' => $lastChunk['population'] ?? null,
            'best_program' => $lastChunk['best_program'] ?? null,
        ];
        $encoded = json_encode($summary, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        if (file_put_contents($summaryPath, $encoded . "\n") === false) {
            throw new \RuntimeException(sprintf('Unable to write summary output to "%s".', $summaryPath));
        }
    }
}
