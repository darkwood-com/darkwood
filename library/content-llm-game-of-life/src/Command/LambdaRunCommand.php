<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\CellularAutomaton\Grid;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use App\Domain\LambdaLanguage\ProgramValidationException;
use App\Domain\LambdaLanguage\ProgramValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:lambda:run',
    description: 'Execute a lambda AST program and stream frames as NDJSON.',
)]
final class LambdaRunCommand extends Command
{
    public function __construct(
        private readonly ProgramExecutorInterface $executor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('ast', null, InputOption::VALUE_REQUIRED, 'Program AST as JSON string.')
            ->addOption('ast-file', null, InputOption::VALUE_REQUIRED, 'Path to a JSON file containing program AST.')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Square grid size.', '16')
            ->addOption('max-frames', null, InputOption::VALUE_REQUIRED, 'Maximum number of generated frames.', '120')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Optional NDJSON output file path.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $size = $this->readPositiveInt((string) $input->getOption('size'), 'size');
            $maxFrames = $this->readPositiveInt((string) $input->getOption('max-frames'), 'max-frames');
            $ast = $this->readAst($input->getOption('ast'), $input->getOption('ast-file'));
            $outputPath = $this->readOptionalOutputPath($input->getOption('output'));

            $program = Program::fromArray($ast, new ProgramValidator(gridSize: $size));
            $frames = $this->executor->execute($program, Grid::empty($size), $maxFrames);

            $encodedLines = [];
            foreach ($frames as $frame) {
                $line = json_encode($frame->toArray(), JSON_THROW_ON_ERROR);
                $encodedLines[] = $line;
                $output->writeln($line);
            }

            if ($outputPath !== null) {
                $this->writeNdjsonOutput($outputPath, $encodedLines);
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

    /**
     * @return array<string,mixed>
     */
    private function readAst(mixed $astOption, mixed $astFileOption): array
    {
        $astJson = null;

        if (\is_string($astOption) && $astOption !== '') {
            $astJson = $astOption;
        } elseif (\is_string($astFileOption) && $astFileOption !== '') {
            if (!is_file($astFileOption)) {
                throw new ProgramValidationException(sprintf('AST file "%s" does not exist.', $astFileOption));
            }

            $astJson = file_get_contents($astFileOption);
            if (!\is_string($astJson)) {
                throw new ProgramValidationException('Unable to read AST file content.');
            }
        }

        if (!\is_string($astJson) || trim($astJson) === '') {
            throw new ProgramValidationException('Provide either --ast or --ast-file with valid JSON.');
        }

        $ast = json_decode($astJson, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($ast)) {
            throw new ProgramValidationException('AST JSON must decode to an object.');
        }

        return $ast;
    }

    private function readOptionalOutputPath(mixed $raw): ?string
    {
        if (!\is_string($raw) || trim($raw) === '') {
            return null;
        }

        return $raw;
    }

    /**
     * @param list<string> $encodedLines
     */
    private function writeNdjsonOutput(string $outputPath, array $encodedLines): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Unable to create output directory "%s".', $directory));
        }

        $payload = implode("\n", $encodedLines);
        if ($payload !== '') {
            $payload .= "\n";
        }

        if (file_put_contents($outputPath, $payload) === false) {
            throw new \RuntimeException(sprintf('Unable to write NDJSON output to "%s".', $outputPath));
        }
    }
}
