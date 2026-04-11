<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Capability\LanguageCapability;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'connect-flow:language:to-lambda',
    description: 'Translate natural language to a lambda expression (local LLM; requires Ollama).',
)]
final class LanguageToLambdaCommand extends Command
{
    public function __construct(
        private readonly LanguageCapability $language,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('text', InputArgument::REQUIRED, 'User intent in natural language');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $text = $input->getArgument('text');
        if (!\is_string($text) || trim($text) === '') {
            $io->error('Pass non-empty text.');

            return Command::FAILURE;
        }

        try {
            $lambda = $this->language->textToLambda($text);
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        $io->writeln($lambda->source());

        return Command::SUCCESS;
    }
}
