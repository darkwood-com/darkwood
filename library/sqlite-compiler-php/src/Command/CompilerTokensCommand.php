<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Common\Token;
use App\Compiler\Driver\Compiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-tokens', description: 'Print preprocessed tokens with locations')]
final class CompilerTokensCommand extends Command
{
    public function __construct(
        private readonly Compiler $compiler,
        private readonly string $includeDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'Path to .c source')
            ->addOption('stats', null, InputOption::VALUE_NONE, 'Print token counts by kind only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (string) $input->getArgument('source');
        if (!is_file($source)) {
            $output->writeln('<error>Source not found.</error>');

            return Command::FAILURE;
        }

        $tokens = $this->compiler->preprocessFile($source, [$this->includeDir]);
        $sm = $this->compiler->sourceManager();

        if ($input->getOption('stats')) {
            $this->printStats($tokens, $output);

            return Command::SUCCESS;
        }

        foreach ($tokens as $token) {
            $output->writeln($this->formatToken($token, $sm));
        }

        $output->writeln('tokenCount: '.count($tokens));

        return Command::SUCCESS;
    }

    /**
     * @param list<Token> $tokens
     */
    private function printStats(array $tokens, OutputInterface $output): void
    {
        /** @var array<string, int> $byKind */
        $byKind = [];
        foreach ($tokens as $token) {
            $name = $token->kind->name;
            $byKind[$name] = ($byKind[$name] ?? 0) + 1;
        }
        arsort($byKind);
        foreach ($byKind as $kind => $count) {
            $output->writeln(sprintf('  %s: %d', $kind, $count));
        }
        $output->writeln('tokenCount: '.count($tokens));
    }

    private function formatToken(Token $token, \App\Compiler\Common\SourceManager $sm): string
    {
        $pos = $sm->lineCol($token->loc);
        $file = $sm->name($token->loc->fileId);

        return sprintf(
            '%s:%d:%d: %s %s',
            $file,
            $pos['line'],
            $pos['col'],
            $token->kind->name,
            $token->spelling,
        );
    }
}
