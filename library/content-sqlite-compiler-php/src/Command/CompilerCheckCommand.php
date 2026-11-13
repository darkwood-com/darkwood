<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-check', description: 'Verify C compiler toolchain prerequisites')]
final class CompilerCheckCommand extends Command
{
    public function __construct(
        private readonly string $compilerDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $failures = 0;
        $this->line($output, 'Platform', php_uname('m') === 'arm64' ? 'OK (arm64)' : 'expected arm64, got '.php_uname('m'), $failures);
        $this->line($output, 'clang', is_executable('/usr/bin/clang') ? 'OK' : 'not found', $failures);
        $this->line($output, 'as', is_executable('/usr/bin/as') ? 'OK' : 'not found', $failures);
        $this->line($output, 'Compiler dir', $this->checkCompilerDir(), $failures);

        $output->writeln('');
        if ($failures === 0) {
            $output->writeln('<info>READY</info>');

            return Command::SUCCESS;
        }

        $output->writeln('<error>NOT READY</error>');

        return Command::FAILURE;
    }

    private function checkCompilerDir(): string
    {
        if (is_dir($this->compilerDir)) {
            return is_writable($this->compilerDir) ? 'OK' : 'not writable';
        }

        if (@mkdir($this->compilerDir, 0775, true)) {
            return 'OK (created)';
        }

        return 'unable to create';
    }

    private function line(OutputInterface $output, string $label, string $status, int &$failures): void
    {
        $ok = str_starts_with($status, 'OK');
        if (!$ok) {
            ++$failures;
        }
        $output->writeln(sprintf('  %-12s %s', $label.':', $ok ? '<info>'.$status.'</info>' : '<error>'.$status.'</error>'));
    }
}
