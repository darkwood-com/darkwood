<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Execution\LambdaExecutionContext;
use App\Infrastructure\Flow\ExecuteLambdaFlowAction;
use App\Infrastructure\Flow\LambdaFlowRuntime;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;

final class LambdaFlowRuntimeTest extends TestCase
{
    public function testFlowRuntimeEvaluatesLambda(): void
    {
        $runtime = new LambdaFlowRuntime(
            new WorkflowRunner(),
            new ExecuteLambdaFlowAction(new \App\Application\Lambda\LambdaEvaluationService(null, [])),
        );

        $out = $runtime->evaluateThroughFlow('λx.x', new LambdaExecutionContext(), [5]);

        self::assertSame(5, $out);
    }
}
