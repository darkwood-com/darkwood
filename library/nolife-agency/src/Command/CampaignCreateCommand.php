<?php

namespace App\Command;

use App\Service\AgentRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

#[AsCommand(name: 'nolife:campaign:create', description: 'Run campaign creation workflow.')]
final class CampaignCreateCommand extends Command
{
    public function __construct(
        private readonly AgentRunner $agentRunner,
        private readonly string $agentRunsDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('project_slug', InputArgument::REQUIRED)
            ->addOption('brief', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectSlug = (string) $input->getArgument('project_slug');
        $briefPath = (string) $input->getOption('brief');
        $brief = $briefPath !== '' && is_file($briefPath) ? (array) Yaml::parseFile($briefPath) : [];
        $runId = uniqid('run_', true);

        $results = $this->agentRunner->runWorkflow($brief, [
            'run_id' => $runId,
            'requester' => 'cli',
            'project_slug' => $projectSlug,
        ]);

        $runDir = rtrim($this->agentRunsDir, '/').'/'.$runId;
        if (!is_dir($runDir)) {
            mkdir($runDir, 0775, true);
        }

        $reportPath = $runDir.'/campaign-report.md';
        $lines = ["# Campaign Run {$projectSlug}", '', "Run ID: `{$runId}`", '', '## Agent Outputs'];
        foreach ($results as $agentName => $agentOutput) {
            $lines[] = sprintf('- `%s`: %s', $agentName, json_encode($agentOutput->payload(), \JSON_THROW_ON_ERROR));
        }
        file_put_contents($reportPath, implode("\n", $lines)."\n");

        $output->writeln(sprintf('Campaign run created: %s', $runId));
        $output->writeln(sprintf('Report: %s', $reportPath));

        return Command::SUCCESS;
    }
}
