<?php

declare(strict_types=1);

namespace App\Command;

use App\Compiler\Common\Decl;
use App\Compiler\Common\FuncDecl;
use App\Compiler\Common\StructDecl;
use App\Compiler\Common\TypedefDecl;
use App\Compiler\Common\UnionDecl;
use App\Compiler\Common\VarDecl;
use App\Compiler\Driver\Compiler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-ast', description: 'Parse and dump the declaration AST')]
final class CompilerAstCommand extends Command
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
            ->addOption('sema', null, InputOption::VALUE_NONE, 'Run semantic analysis before dump')
            ->addOption('stats', null, InputOption::VALUE_NONE, 'Print declaration counts only');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $source = (string) $input->getArgument('source');
        if (!is_file($source)) {
            $output->writeln('<error>Source not found.</error>');

            return Command::FAILURE;
        }

        $tokens = $this->compiler->preprocessFile($source, [$this->includeDir]);
        if ($this->compiler->diagnostics()->hasErrors()) {
            foreach ($this->compiler->diagnostics()->diagnostics as $diag) {
                $output->writeln('<error>'.$this->formatDiagnostic($diag).'</error>');
            }

            return Command::FAILURE;
        }

        $decls = $this->compiler->parseTokens($tokens);
        if ($this->compiler->diagnostics()->hasErrors()) {
            foreach ($this->compiler->diagnostics()->diagnostics as $diag) {
                $output->writeln('<error>'.$this->formatDiagnostic($diag).'</error>');
            }

            return Command::FAILURE;
        }

        if ($input->getOption('sema')) {
            $decls = $this->compiler->analyze($decls);
            if ($this->compiler->diagnostics()->hasErrors()) {
                foreach ($this->compiler->diagnostics()->diagnostics as $diag) {
                    $output->writeln('<error>'.$this->formatDiagnostic($diag).'</error>');
                }

                return Command::FAILURE;
            }
        }

        if ($input->getOption('stats')) {
            $this->printStats($decls, $output);

            return Command::SUCCESS;
        }

        foreach ($decls as $decl) {
            $output->writeln($this->dumpDecl($decl, 0));
        }

        return Command::SUCCESS;
    }

    /**
     * @param list<Decl> $decls
     */
    private function printStats(array $decls, OutputInterface $output): void
    {
        $counts = [
            'FuncDecl' => 0,
            'VarDecl' => 0,
            'StructDecl' => 0,
            'UnionDecl' => 0,
            'TypedefDecl' => 0,
            'EnumDecl' => 0,
            'other' => 0,
        ];
        foreach ($decls as $decl) {
            $key = match (true) {
                $decl instanceof FuncDecl => 'FuncDecl',
                $decl instanceof VarDecl => 'VarDecl',
                $decl instanceof StructDecl => 'StructDecl',
                $decl instanceof UnionDecl => 'UnionDecl',
                $decl instanceof TypedefDecl => 'TypedefDecl',
                $decl instanceof \App\Compiler\Common\EnumDecl => 'EnumDecl',
                default => 'other',
            };
            ++$counts[$key];
        }
        foreach ($counts as $kind => $count) {
            if ($count > 0) {
                $output->writeln(sprintf('  %s: %d', $kind, $count));
            }
        }
        $output->writeln('declCount: '.count($decls));
    }

    private function formatDiagnostic(\App\Compiler\Common\Diagnostic $diag): string
    {
        return $diag->format($this->compiler->sourceManager());
    }

    private function dumpDecl(Decl $decl, int $indent): string
    {
        $pad = str_repeat('  ', $indent);

        return match (true) {
            $decl instanceof FuncDecl => $pad.'FuncDecl '.$decl->name
                .' params='.count($decl->params)
                .($decl->variadic ? ' ...' : '')
                .($decl->body !== null ? ' body' : ' proto'),
            $decl instanceof VarDecl => $pad.'VarDecl '.$decl->name.($decl->isGlobal ? ' global' : ' local'),
            $decl instanceof StructDecl => $pad.'StructDecl '.($decl->name ?? '<anon>'),
            $decl instanceof UnionDecl => $pad.'UnionDecl '.($decl->name ?? '<anon>'),
            $decl instanceof TypedefDecl => $pad.'TypedefDecl '.$decl->name,
            $decl instanceof \App\Compiler\Common\EnumDecl => $pad.'EnumDecl '.($decl->name ?? '<anon>')
                .' cases='.count($decl->enumType->cases),
            default => $pad.$decl::class,
        };
    }
}
