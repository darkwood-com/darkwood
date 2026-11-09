<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow;

use App\Domain\Execution\LambdaExecutionContext;
use Navi\Application\Workflow\WorkflowRunner;
use Navi\Domain\Execution\Context;

/**
 * Public runtime facade: Navi owns the execution shell, Flow owns the lambda
 * execution step inside that shell.
 */
final readonly class LambdaFlowRuntime
{
    public function __construct(
        private WorkflowRunner $runner,
        private ExecuteLambdaFlowAction $executeLambdaFlow,
    ) {
    }

    /**
     * @param list<mixed> $arguments
     */
    public function evaluateThroughFlow(string $expression, LambdaExecutionContext $context, array $arguments = []): mixed
    {
        $result = $this->runner->run(
            Context::fromArray([
                'lambda_expression' => $expression,
                'lambda_context' => $context,
                'lambda_arguments' => array_values($arguments),
            ]),
            [$this->executeLambdaFlow],
        );

        return $result->state()->context()->get('lambda_result');
    }
}
