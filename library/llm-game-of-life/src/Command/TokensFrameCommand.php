<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Tokenization\FrameTokenizer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tokens:frame',
    description: 'Tokenize a serialized frame JSON payload.',
)]
final class TokensFrameCommand extends Command
{
    public function __construct(private readonly FrameTokenizer $tokenizer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('input', null, InputOption::VALUE_REQUIRED, 'Path to a frame JSON file.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $frame = $this->readJsonFile($input->getOption('input'));
            $output->writeln(json_encode($this->tokenizer->tokenizeFrame($frame), JSON_THROW_ON_ERROR));
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @return array<string,mixed>
     */
    private function readJsonFile(mixed $path): array
    {
        if (!\is_string($path) || trim($path) === '') {
            throw new \InvalidArgumentException('Provide --input with a frame JSON file.');
        }

        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('Frame file "%s" does not exist.', $path));
        }

        $raw = file_get_contents($path);
        if (!\is_string($raw)) {
            throw new \InvalidArgumentException(sprintf('Unable to read frame file "%s".', $path));
        }

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \InvalidArgumentException('Frame JSON must decode to an object.');
        }

        return $decoded;
    }
}
