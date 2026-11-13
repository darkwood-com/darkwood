<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Platform\Toolchain;
use App\Flow\CompileFlow;
use App\Flow\Model\CompileState;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-run', description: 'Build and run a C program with our compiler')]
final class CompilerRunCommand extends Command
{
    public function __construct(
        private readonly CompileFlow $compileFlow,
        private readonly Toolchain $toolchain,
        private readonly string $includeDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('source', InputArgument::REQUIRED, 'Path to .c source');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (string) $input->getArgument('source');
        $base = pathinfo($source, PATHINFO_FILENAME);
        $prefix = $this->toolchain->makeWorkPath($base);

        $state = new CompileState($source, $prefix.'.s');
        $state->outputObjectPath = $prefix.'.o';
        $state->outputExecutablePath = $prefix;
        $state->includePaths = [$this->includeDir];

        try {
            $this->compileFlow->run($state, link: true);
        } catch (\Throwable $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        $run = $this->toolchain->runExecutable($prefix);
        if ($run->stdout !== '') {
            $output->write($run->stdout);
        }
        if ($run->stderr !== '') {
            $output->write($run->stderr);
        }

        return $run->exitCode;
    }
}
