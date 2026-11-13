<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Platform\Toolchain;
use App\Flow\CompileFlow;
use App\Flow\Model\CompileState;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-fixtures', description: 'Run compiler regression fixtures')]
final class CompilerFixturesCommand extends Command
{
    public function __construct(
        private readonly CompileFlow $compileFlow,
        private readonly Toolchain $toolchain,
        private readonly string $projectDir,
        private readonly string $includeDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('filter', 'f', InputOption::VALUE_REQUIRED, 'Run only fixtures matching this substring');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filter = $input->getOption('filter');
        $dir = $this->projectDir.'/fixtures/compiler';
        $sources = glob($dir.'/*.c') ?: [];
        sort($sources);

        $passed = 0;
        $failed = 0;
        $skipped = 0;

        foreach ($sources as $source) {
            $base = pathinfo($source, PATHINFO_FILENAME);
            if ($filter !== null && !str_contains($base, (string) $filter)) {
                continue;
            }

            $metaPath = $dir.'/'.$base.'.json';
            if (!is_file($metaPath)) {
                ++$skipped;
                continue;
            }

            /** @var array<string, mixed> $meta */
            $meta = json_decode((string) file_get_contents($metaPath), true, flags: JSON_THROW_ON_ERROR);

            $prefix = $this->toolchain->makeWorkPath($base);
            $state = new CompileState($source, $prefix.'.s');
            $state->outputObjectPath = $prefix.'.o';
            $state->outputExecutablePath = $prefix;
            $state->includePaths = [$this->includeDir];

            try {
                $this->compileFlow->run($state, link: true);
            } catch (\Throwable $e) {
                $output->writeln("<error>FAIL {$base}: {$e->getMessage()}</error>");
                ++$failed;
                continue;
            }

            $assembly = (string) file_get_contents($state->outputAsmPath);
            $errors = $this->assertAssembly($assembly, $meta);
            if ($errors !== []) {
                foreach ($errors as $err) {
                    $output->writeln("<error>FAIL {$base}: {$err}</error>");
                }
                ++$failed;
                continue;
            }

            $run = $this->toolchain->runExecutable($prefix);
            $expectedExit = (int) ($meta['exitCode'] ?? 0);
            $expectedStdout = (string) ($meta['stdout'] ?? '');

            if (!empty($meta['compareWithClang'])) {
                $clangExe = $prefix.'_clang';
                $clang = $this->toolchain->clangCompileProbe($source, $clangExe, ['-I', $this->includeDir]);
                if ($clang->exitCode !== 0) {
                    $output->writeln("<error>FAIL {$base}: clang compile failed</error>");
                    ++$failed;
                    continue;
                }
                $clangRun = $this->toolchain->runExecutable($clangExe);
                $expectedExit = $clangRun->exitCode;
                $expectedStdout = $clangRun->stdout;
            }

            if ($run->exitCode !== $expectedExit) {
                $output->writeln("<error>FAIL {$base}: exit {$run->exitCode}, expected {$expectedExit}</error>");
                ++$failed;
                continue;
            }

            $stdout = $run->stdout;
            if ($stdout !== $expectedStdout) {
                $output->writeln("<error>FAIL {$base}: stdout mismatch</error>");
                ++$failed;
                continue;
            }

            $output->writeln("<info>PASS {$base}</info>");
            if ($state->compilation !== null) {
                foreach ($state->compilation->metrics as $name => $value) {
                    $output->writeln(sprintf('  %s: %s', $name, $value));
                }
                foreach ($state->timings as $name => $ms) {
                    $output->writeln(sprintf('  %s: %.2f ms', $name, $ms));
                }
            }
            $output->writeln('  peak_memory: '.memory_get_peak_usage(true));
            ++$passed;
        }

        $output->writeln('');
        $output->writeln("Passed: {$passed}, Failed: {$failed}, Skipped: {$skipped}");

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return list<string>
     */
    private function assertAssembly(string $assembly, array $meta): array
    {
        $errors = [];

        /** @var list<string> $contains */
        $contains = $meta['assemblyContains'] ?? [];
        foreach ($contains as $needle) {
            if (!str_contains($assembly, $needle)) {
                $errors[] = "assembly missing '{$needle}'";
            }
        }

        /** @var list<string> $notContains */
        $notContains = $meta['assemblyNotContains'] ?? [];
        foreach ($notContains as $needle) {
            if (str_contains($assembly, $needle)) {
                $errors[] = "assembly must not contain '{$needle}'";
            }
        }

        return $errors;
    }
}
