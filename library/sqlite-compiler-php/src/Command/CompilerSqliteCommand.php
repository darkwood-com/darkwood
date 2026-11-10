<?php

declare(strict_types=1);

namespace App\Command;

use App\Flow\Model\SqliteValidationState;
use App\Flow\SqliteValidationFlow;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-sqlite', description: 'Compile SQLite amalgamation and run smoke test')]
final class CompilerSqliteCommand extends Command
{
    public function __construct(
        private readonly SqliteValidationFlow $sqliteValidationFlow,
        private readonly string $projectDir,
        private readonly string $workDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $amalgamation = $this->projectDir.'/var/compiler/sqlite/sqlite3.c';
        $harness = $this->projectDir.'/fixtures/compiler/sqlite_smoke_test.c';

        if (!is_file($amalgamation)) {
            $output->writeln('<error>Missing SQLite amalgamation at var/compiler/sqlite/sqlite3.c</error>');

            return Command::FAILURE;
        }

        $runDir = $this->workDir.'/sqlite_'.date('Ymd_His');
        if (!mkdir($runDir, 0775, true) && !is_dir($runDir)) {
            $output->writeln('<error>Unable to create work directory.</error>');

            return Command::FAILURE;
        }

        $state = new SqliteValidationState($amalgamation, $harness, $runDir);

        try {
            $this->sqliteValidationFlow->run($state);
        } catch (\Throwable $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>SQLITE SMOKE TEST PASSED</info>');
        $output->writeln('stdout: '.$state->stdout);
        foreach ($state->metrics as $name => $value) {
            $output->writeln(sprintf('  %s: %s', $name, $value));
        }
        foreach ($state->timings as $name => $ms) {
            $output->writeln(sprintf('  %s: %.2f ms', $name, $ms));
        }
        if (!isset($state->metrics['assemblyBytes'])) {
            $asmPath = $state->sqliteAsmPath;
            if ($asmPath !== '' && is_file($asmPath)) {
                $output->writeln('  assemblyBytes: '.filesize($asmPath));
            }
        }
        $output->writeln('  peak_memory: '.memory_get_peak_usage(true));

        return Command::SUCCESS;
    }
}
