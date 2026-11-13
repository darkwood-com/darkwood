<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Driver\CompileOptions;
use App\Compiler\Driver\Compiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-compile', description: 'Compile C source to ARM64 assembly with our compiler')]
final class CompilerCompileCommand extends Command
{
    public function __construct(
        private readonly Compiler $compiler,
        private readonly string $workDir,
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
        if (!is_file($source)) {
            $output->writeln('<error>Source not found.</error>');

            return Command::FAILURE;
        }

        $base = pathinfo($source, PATHINFO_FILENAME);
        $asm = $this->workDir.'/'.$base.'_'.uniqid('', true).'.s';

        $result = $this->compiler->compile(new CompileOptions(
            sourcePath: $source,
            outputAsmPath: $asm,
            includePaths: [$this->includeDir],
        ));

        foreach ($result->timingsMs as $name => $ms) {
            $output->writeln(sprintf('  %s: %.2f ms', $name, $ms));
        }
        foreach ($result->metrics as $name => $value) {
            $output->writeln(sprintf('  %s: %s', $name, $value));
        }

        if (!$result->success) {
            foreach ($result->diagnostics as $diag) {
                $output->writeln('<error>'.$diag->format($this->compiler->sourceManager()).'</error>');
            }

            return Command::FAILURE;
        }

        $output->writeln('<info>Wrote '.$asm.'</info>');

        return Command::SUCCESS;
    }
}
