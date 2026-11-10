<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Driver\CompileOptions;
use App\Compiler\Driver\Compiler;
use App\Compiler\Platform\Toolchain;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-compare-clang', description: 'Compare our assembly with clang -S output')]
final class CompilerCompareClangCommand extends Command
{
    public function __construct(
        private readonly Compiler $compiler,
        private readonly Toolchain $toolchain,
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
        $ourAsm = $this->workDir.'/'.$base.'_our.s';
        $clangAsm = $this->workDir.'/'.$base.'_clang.s';

        $result = $this->compiler->compile(new CompileOptions(
            sourcePath: $source,
            outputAsmPath: $ourAsm,
            includePaths: [$this->includeDir],
        ));

        if (!$result->success) {
            foreach ($result->diagnostics as $diag) {
                $output->writeln('<error>'.$diag->format($this->compiler->sourceManager()).'</error>');
            }

            return Command::FAILURE;
        }

        $clang = $this->toolchain->clangEmitAssembly($source, $clangAsm, ['-I', $this->includeDir]);
        if ($clang->exitCode !== 0) {
            $output->writeln('<error>clang failed: '.$clang->stderr.'</error>');

            return Command::FAILURE;
        }

        $ourText = (string) file_get_contents($ourAsm);
        $clangText = (string) file_get_contents($clangAsm);

        $ourLabels = $this->extractLabels($ourText);
        $clangLabels = $this->extractLabels($clangText);

        $onlyOurs = array_diff($ourLabels, $clangLabels);
        $onlyClang = array_diff($clangLabels, $ourLabels);
        $shared = array_intersect($ourLabels, $clangLabels);

        $output->writeln('Our assembly: '.$ourAsm.' ('.strlen($ourText).' bytes)');
        $output->writeln('Clang assembly: '.$clangAsm.' ('.strlen($clangText).' bytes)');
        $output->writeln('Shared labels: '.count($shared));
        $output->writeln('Only in ours: '.count($onlyOurs));
        foreach (array_slice($onlyOurs, 0, 10) as $label) {
            $output->writeln('  '.$label);
        }
        $output->writeln('Only in clang: '.count($onlyClang));
        foreach (array_slice($onlyClang, 0, 10) as $label) {
            $output->writeln('  '.$label);
        }

        $ourDirectives = $this->countDirectives($ourText);
        $clangDirectives = $this->countDirectives($clangText);
        $output->writeln('Directive counts (ours vs clang):');
        foreach (array_unique(array_merge(array_keys($ourDirectives), array_keys($clangDirectives))) as $name) {
            $output->writeln(sprintf('  %s: %d / %d', $name, $ourDirectives[$name] ?? 0, $clangDirectives[$name] ?? 0));
        }

        return Command::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function extractLabels(string $asm): array
    {
        preg_match_all('/^(_?\w+):/m', $asm, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * @return array<string, int>
     */
    private function countDirectives(string $asm): array
    {
        preg_match_all('/^\s*\.(\w+)/m', $asm, $matches);
        $counts = [];
        foreach ($matches[1] ?? [] as $name) {
            $counts[$name] = ($counts[$name] ?? 0) + 1;
        }

        return $counts;
    }
}
