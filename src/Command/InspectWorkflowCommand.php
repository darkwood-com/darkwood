<?php

declare(strict_types=1);

namespace App\Command;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\MergeContextAction;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'navi:workflow:inspect', description: 'Emit a structural workflow snapshot.')]
final class InspectWorkflowCommand extends Command
{
    public function __construct(private readonly WorkflowRunner $workflowRunner)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->workflowRunner->run(
            Context::fromArray([
                'channel' => 'public',
                'source' => 'cli',
            ]),
            [
                new MergeContextAction(
                    ActionName::fromString('register_input'),
                    ['state' => 'received']
                ),
                new MergeContextAction(
                    ActionName::fromString('finalize_execution'),
                    ['status' => 'completed']
                ),
            ]
        );

        $payload = [
            'executionId' => $result->state()->executionId()->toString(),
            'context' => $result->state()->context()->toArray(),
            'events' => array_map(
                static fn ($event) => [
                    'name' => $event->name(),
                    'payload' => $event->payload(),
                ],
                $result->state()->events()
            ),
        ];

        $output->writeln(json_encode($payload, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return Command::SUCCESS;
    }
}
