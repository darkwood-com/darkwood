<?php

namespace App\Tests\Integration\Flow;

use App\Integration\Flow\NullFlowWorkflowClient;
use App\Integration\Flow\WorkflowDispatch;
use PHPUnit\Framework\TestCase;

final class NullFlowWorkflowClientTest extends TestCase
{
    public function testDispatchReturnsNoopResult(): void
    {
        $result = (new NullFlowWorkflowClient())->dispatch(new WorkflowDispatch('client_website', 'run-1', []));
        self::assertSame('noop', $result->status);
        self::assertSame('run-1', $result->runId);
        self::assertSame('client_website', $result->workflowKey);
    }
}
