<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramValidator;
use App\Domain\Tokenization\ProgramTokenizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tokens:program',
    description: 'Tokenize a lambda AST program JSON payload.',
)]
final class TokensProgramCommand extends Command
{
    public function __construct(private readonly ProgramTokenizer $tokenizer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('ast', null, InputOption::VALUE_REQUIRED, 'Program AST as JSON string.')
            ->addOption('ast-file', null, InputOption::VALUE_REQUIRED, 'Path to a JSON file containing program AST.')
            ->addOption('size', null, InputOption::VALUE_REQUIRED, 'Square grid size for AST validation.', '16');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $size = $this->readPositiveInt((string) $input->getOption('size'));
            $ast = $this->readAst($input->getOption('ast'), $input->getOption('ast-file'));
            $program = Program::fromArray($ast, new ProgramValidator(gridSize: $size));

            $output->writeln(json_encode($this->tokenizer->tokenizeProgram($program), JSON_THROW_ON_ERROR));
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function readPositiveInt(string $raw): int
    {
        if (!ctype_digit($raw) || (int) $raw <= 0) {
            throw new \InvalidArgumentException('Option --size must be a positive integer.');
        }

        return (int) $raw;
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
                throw new \InvalidArgumentException(sprintf('AST file "%s" does not exist.', $astFileOption));
            }

            $astJson = file_get_contents($astFileOption);
            if (!\is_string($astJson)) {
                throw new \InvalidArgumentException('Unable to read AST file content.');
            }
        }

        if (!\is_string($astJson) || trim($astJson) === '') {
            throw new \InvalidArgumentException('Provide either --ast or --ast-file with valid JSON.');
        }

        $ast = json_decode($astJson, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($ast)) {
            throw new \InvalidArgumentException('AST JSON must decode to an object.');
        }

        return $ast;
    }
}
