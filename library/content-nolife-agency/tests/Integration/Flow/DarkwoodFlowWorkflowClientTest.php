<?php

namespace App\Tests\Integration\Flow;

use App\Integration\Flow\DarkwoodFlowWorkflowClient;
use App\Integration\Flow\WorkflowDispatch;
use PHPUnit\Framework\TestCase;

final class DarkwoodFlowWorkflowClientTest extends TestCase
{
    public function testDispatchCompletesInProcess(): void
    {
        $dispatch = new WorkflowDispatch('client_website', 'run-2', [], [], [
            ['key' => 'step_1', 'to' => 'brief_created'],
            ['key' => 'step_2', 'to' => 'human_approval_required'],
        ]);
        $result = (new DarkwoodFlowWorkflowClient())->dispatch($dispatch);
        self::assertSame('completed', $result->status);
        self::assertSame('human_approval_required', $result->finalState);
        self::assertSame(['step_1', 'step_2'], $result->completedSteps);
    }
}
