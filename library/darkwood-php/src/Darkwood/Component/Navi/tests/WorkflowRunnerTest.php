<?php

declare(strict_types=1);

namespace Navi\Tests;

use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\Context;
use Navi\Infrastructure\Action\MergeContextAction;
use PHPUnit\Framework\TestCase;

final class WorkflowRunnerTest extends TestCase
{
    public function testRunMergesActionPayloadsAndRecordsCompletedActionsAndEvents()
    {
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

        self::assertSame('prepared', $result->state()->context()->get('phase'), 'Workflow should merge the first action payload into the context.');
        self::assertSame('done', $result->state()->context()->get('status'), 'Workflow should merge the second action payload into the context.');
        self::assertCount(2, $result->state()->completedActions(), 'Workflow should record every completed action.');
        self::assertCount(2, $result->state()->events(), 'Workflow should record one event per action.');
    }
}
