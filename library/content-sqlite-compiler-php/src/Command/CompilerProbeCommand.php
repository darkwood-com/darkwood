<?php

declare(strict_types=1);

namespace App\Command;

use App\Flow\Model\ProbeState;
use App\Flow\ProbeRunFlow;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:compiler-probe', description: 'Run a C compiler fixture probe')]
final class CompilerProbeCommand extends Command
{
    public function __construct(
        private readonly ProbeRunFlow $probeRunFlow,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fixture', InputArgument::REQUIRED, 'Path to .c fixture')
            ->addOption('expect-exit', null, InputOption::VALUE_REQUIRED, 'Expected exit code', '0')
            ->addOption('expect-stdout', null, InputOption::VALUE_REQUIRED, 'Expected stdout', '');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixtureArg = (string) $input->getArgument('fixture');
        $fixturePath = str_starts_with($fixtureArg, '/') ? $fixtureArg : $this->projectDir.'/'.$fixtureArg;

        $expectedPath = preg_replace('/\.c$/', '.expected.json', $fixturePath) ?: $fixturePath.'.expected.json';
        $expectedExit = (int) $input->getOption('expect-exit');
        $expectedStdout = (string) $input->getOption('expect-stdout');

        if (is_file($expectedPath)) {
            /** @var array{exitCode?: int, stdout?: string} $meta */
            $meta = json_decode((string) file_get_contents($expectedPath), true, 512, JSON_THROW_ON_ERROR);
            $expectedExit = $meta['exitCode'] ?? $expectedExit;
            $expectedStdout = $meta['stdout'] ?? $expectedStdout;
        }

        $state = new ProbeState(
            sourcePath: $fixturePath,
            expectedExit: $expectedExit,
            expectedStdout: $expectedStdout,
        );

        try {
            $this->probeRunFlow->run($state);
        } catch (\Throwable $e) {
            $output->writeln('<error>'.$e->getMessage().'</error>');

            return Command::FAILURE;
        }

        $output->writeln('<info>PASS</info>');
        foreach ($state->timings as $name => $ms) {
            $output->writeln(sprintf('  %s: %.2f ms', $name, $ms));
        }

        return Command::SUCCESS;
    }
}
