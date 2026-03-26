<?php

declare(strict_types=1);

namespace App\Tests\Application\Workflow;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\MergeContextAction;
use PHPUnit\Framework\TestCase;

final class WorkflowRunnerTest extends TestCase
{
    public function testItRecordsAMinimalWorkflowExecution(): void
    {
        $runner = new WorkflowRunner();

        $result = $runner->run(
            Context::fromArray([
                'channel' => 'test',
            ]),
            [
                new MergeContextAction(ActionName::fromString('register_input'), ['state' => 'received']),
                new MergeContextAction(ActionName::fromString('finalize_execution'), ['status' => 'completed']),
            ]
        );

        self::assertSame('received', $result->state()->context()->get('state'));
        self::assertSame('completed', $result->state()->context()->get('status'));
        self::assertCount(2, $result->state()->completedActions());
        self::assertCount(2, $result->state()->events());
        self::assertNotSame('', $result->state()->executionId()->toString());
    }
}
