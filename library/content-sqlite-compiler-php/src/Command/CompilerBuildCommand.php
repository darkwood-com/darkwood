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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-build', description: 'Compile, assemble, and link with our compiler')]
final class CompilerBuildCommand extends Command
{
    public function __construct(
        private readonly CompileFlow $compileFlow,
        private readonly Toolchain $toolchain,
        private readonly string $workDir,
        private readonly string $includeDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('source', InputArgument::REQUIRED, 'Path to .c source')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output executable path')
            ->addOption('include', 'I', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Include path', [])
            ->addOption('define', 'D', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Preprocessor define', []);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (string) $input->getArgument('source');
        if (!is_file($source)) {
            $output->writeln('<error>Source not found.</error>');

            return Command::FAILURE;
        }

        $base = pathinfo($source, PATHINFO_FILENAME);
        $prefix = $this->toolchain->makeWorkPath($base);
        $asm = $prefix.'.s';
        $obj = $prefix.'.o';
        $exe = (string) ($input->getOption('output') ?? $prefix);

        /** @var list<string> $includes */
        $includes = array_merge([$this->includeDir], $input->getOption('include'));

        /** @var list<string> $definesOpt */
        $definesOpt = $input->getOption('define');
        $defines = [];
        foreach ($definesOpt as $def) {
            if (str_contains($def, '=')) {
                [$name, $value] = explode('=', $def, 2);
                $defines[$name] = $value;
            } else {
                $defines[$def] = '1';
            }
        }

        $state = new CompileState($source, $asm);
        $state->outputObjectPath = $obj;
        $state->outputExecutablePath = $exe;
        $state->includePaths = $includes;
        $state->defines = $defines;

        try {
            $this->compileFlow->run($state, link: true);
        } catch (\Throwable $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        foreach ($state->timings as $name => $ms) {
            $output->writeln(sprintf('  %s: %.2f ms', $name, $ms));
        }

        if ($state->compilation !== null) {
            foreach ($state->compilation->metrics as $name => $value) {
                $output->writeln(sprintf('  %s: %s', $name, $value));
            }
        }

        $output->writeln('<info>Built '.$exe.'</info>');

        return Command::SUCCESS;
    }
}
