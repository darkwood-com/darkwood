<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;
use Navi\Infrastructure\Action\MergeContextAction;

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . sprintf(' Expected %s, got %s.', var_export($expected, true), var_export($actual, true)));
    }
}

$runner = new WorkflowRunner();

$result = $runner->run(
    Context::fromArray([
        'execution' => 'test',
    ]),
    [
        new MergeContextAction(ActionName::fromString('prepare'), ['phase' => 'prepared']),
        new MergeContextAction(ActionName::fromString('complete'), ['status' => 'done']),
    ]
);

assertSameValue('prepared', $result->state()->context()->get('phase'), 'Workflow should merge the first action payload into the context.');
assertSameValue('done', $result->state()->context()->get('status'), 'Workflow should merge the second action payload into the context.');
assertSameValue(2, count($result->state()->completedActions()), 'Workflow should record every completed action.');
assertSameValue(2, count($result->state()->events()), 'Workflow should record one event per action.');

echo "WorkflowRunnerTest passed\n";
