<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Execution\LambdaExecutionContext;
use App\Infrastructure\ExpressionLanguage\LambdaExpressionLanguageFactory;
use App\Infrastructure\Flow\ExecuteLambdaFlowAction;
use App\Infrastructure\Flow\LambdaFlowRuntime;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;

final class LambdaExpressionLanguageIntegrationTest extends TestCase
{
    public function testLambdaFunctionEvaluatesExpression(): void
    {
        $runtime = new LambdaFlowRuntime(
            new WorkflowRunner(),
            new ExecuteLambdaFlowAction(new \App\Application\Lambda\LambdaEvaluationService(null, [])),
        );
        $factory = new LambdaExpressionLanguageFactory($runtime);
        $lang = $factory->create(new LambdaExecutionContext());

        $out = $lang->evaluate("lambda('λx.x', 5)", []);

        self::assertSame(5, $out);
    }
}
