<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Training\UnsupervisedTrainingService;
use App\Domain\LambdaLanguage\ProgramValidationException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:train:unsupervised',
    description: 'Run unsupervised evolution and stream best individual per generation.',
)]
final class TrainUnsupervisedCommand extends Command
{
    public function __construct(
        private readonly UnsupervisedTrainingService $training,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Square grid size.', '16')
            ->addOption('population', null, InputOption::VALUE_REQUIRED, 'Population size.', '40')
            ->addOption('generations', null, InputOption::VALUE_REQUIRED, 'Generation count.', '25')
            ->addOption('seed', null, InputOption::VALUE_REQUIRED, 'Deterministic random seed.', '42')
            ->addOption('max-frames', null, InputOption::VALUE_REQUIRED, 'Frame budget per program execution.', '30')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Optional NDJSON output file path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $size = $this->readPositiveInt((string) $input->getOption('size'), 'size');
            $population = $this->readPositiveInt((string) $input->getOption('population'), 'population');
            $generations = $this->readPositiveInt((string) $input->getOption('generations'), 'generations');
            $seed = $this->readPositiveInt((string) $input->getOption('seed'), 'seed');
            $maxFrames = $this->readPositiveInt((string) $input->getOption('max-frames'), 'max-frames');
            $outputPath = $this->readOptionalOutputPath($input->getOption('output'));

            $chunks = [];
            foreach ($this->training->train($size, $population, $generations, $seed, $maxFrames) as $chunk) {
                $chunks[] = $chunk;
                $output->writeln(json_encode($chunk, JSON_THROW_ON_ERROR));
            }

            if ($outputPath !== null) {
                $this->writeOutputs($outputPath, $chunks);
            }
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
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

        $ndjson = implode(
            "\n",
            array_map(
                static fn (array $chunk): string => json_encode($chunk, JSON_THROW_ON_ERROR),
                $chunks,
            ),
        );
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
            'final_generation' => $lastChunk['generation'] ?? null,
            'best_fitness' => $lastChunk['best_fitness'] ?? null,
            'average_fitness' => $lastChunk['average_fitness'] ?? null,
            'seed' => $lastChunk['seed'] ?? null,
            'population' => $lastChunk['population'] ?? null,
            'best_program' => $lastChunk['best_program'] ?? null,
            'best_frame' => $lastChunk['best_frame'] ?? null,
        ];

        $encodedSummary = json_encode($summary, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        if (file_put_contents($summaryPath, $encodedSummary . "\n") === false) {
            throw new \RuntimeException(sprintf('Unable to write summary output to "%s".', $summaryPath));
        }
    }
}
