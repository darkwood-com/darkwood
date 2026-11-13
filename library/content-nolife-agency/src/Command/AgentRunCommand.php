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

#[AsCommand(name: 'nolife:agent:run', description: 'Run a single nolife agent.')]
final class AgentRunCommand extends Command
{
    public function __construct(
        private readonly AgentRunner $agentRunner,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('agent', InputArgument::REQUIRED)
            ->addOption('input', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $agent = (string) $input->getArgument('agent');
        $inputFile = (string) $input->getOption('input');
        $payload = $inputFile !== '' && is_file($inputFile) ? (array) Yaml::parseFile($inputFile) : [];

        $result = $this->agentRunner->runSingle($agent, $payload, [
            'requester' => 'cli',
        ]);

        $output->writeln(json_encode($result->payload(), \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
