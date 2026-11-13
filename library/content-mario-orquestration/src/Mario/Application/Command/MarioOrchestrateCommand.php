<?php

namespace App\Mario\Application\Command;

use App\Mario\Infrastructure\AI\MarioAgentOrchestrator;
use App\Mario\Infrastructure\Export\GodotLevelJsonExporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:mario:orchestrate', description: 'Run a Symfony AI multi-agent orchestration demo for mario-like levels.')]
final class MarioOrchestrateCommand extends Command
{
    public function __construct(
        private readonly MarioAgentOrchestrator $orchestrator,
        private readonly GodotLevelJsonExporter $exporter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('prompt', null, InputOption::VALUE_REQUIRED, 'User prompt for orchestration.', 'Generate a Mario-like platformer level')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Output json path.', 'var/mario-orchestration/level.json')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Do not write output file.')
            ->addOption('fake', null, InputOption::VALUE_NONE, 'Force deterministic fake mode.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $prompt = (string) $input->getOption('prompt');
        $outputPath = (string) $input->getOption('output');
        $dryRun = (bool) $input->getOption('dry-run');
        $forceFake = (bool) $input->getOption('fake');

        $io->writeln('=== Mario Orchestration ===');
        $io->writeln(sprintf('Prompt: %s', $prompt));
        $io->writeln('Orchestrator: started');

        $result = $this->orchestrator->orchestrate($prompt, $forceFake);

        $io->writeln(sprintf('Mode: %s', $result['fake_mode'] ? 'fake (deterministic)' : 'openai'));
        $io->writeln(sprintf('Selected agent: %s', $result['selected_agent']));
        $io->writeln('Result:');
        $io->writeln($result['result_text']);

        if (!$result['validation']->isValid()) {
            $io->error('Validation failed.');
            $io->listing($result['validation']->errors());

            return Command::FAILURE;
        }

        if ($dryRun) {
            $io->note(sprintf('Dry-run enabled, output not written: %s', $outputPath));

            return Command::SUCCESS;
        }

        $this->exporter->write($result['payload'], $outputPath);
        $io->success(sprintf('Level exported to %s', $outputPath));

        return Command::SUCCESS;
    }
}
