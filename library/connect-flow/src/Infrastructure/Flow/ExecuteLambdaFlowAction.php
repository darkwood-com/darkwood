<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow;

use App\Application\Lambda\LambdaEvaluationService;
use App\Domain\Execution\LambdaExecutionContext;
use Flow\Driver\FiberDriver;
use Flow\DriverInterface;
use Flow\Flow\LambdaFlow;
use Flow\Ip;
use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;

/**
 * Navi action: execute a lambda program through Flow's LambdaFlow/LambdaJob.
 */
final readonly class ExecuteLambdaFlowAction implements Action
{
    public function __construct(
        private LambdaEvaluationService $evaluation,
        private ?DriverInterface $driver = null,
    ) {
    }

    public function name(): ActionName
    {
        return ActionName::fromString('lambda.flow.execute');
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $expression = $context->get('lambda_expression');
        $lambdaContext = $context->get('lambda_context');
        $arguments = $context->get('lambda_arguments', []);

        if (!\is_string($expression) || $expression === '') {
            throw new \InvalidArgumentException('Context key lambda_expression must be a non-empty string.');
        }

        if (!$lambdaContext instanceof LambdaExecutionContext) {
            throw new \InvalidArgumentException('Context key lambda_context must contain a LambdaExecutionContext.');
        }

        if (!\is_array($arguments)) {
            throw new \InvalidArgumentException('Context key lambda_arguments must be a list.');
        }

        $appliedArguments = $this->evaluation->appliedArguments($lambdaContext, array_values($arguments));
        if ($appliedArguments === []) {
            throw new \InvalidArgumentException(
                'At least one lambda argument or configured primitive binding is required.'
            );
        }

        $result = $this->runLambdaFlow(
            $this->evaluation->normalizeSource($expression),
            $appliedArguments,
        );

        return ActionResult::continueWith(
            $context->merge([
                'lambda_result' => $this->evaluation->finalizeResult($result),
            ]),
            Event::fromName('lambda.executed', ['expression' => $expression]),
        );
    }

    /**
     * @param list<mixed> $appliedArguments
     */
    private function runLambdaFlow(string $expression, array $appliedArguments): mixed
    {
        $result = null;
        $flow = new LambdaFlow(
            $expression,
            driver: $this->driver ?? new FiberDriver(),
        );

        foreach (array_slice($appliedArguments, 1) as $argument) {
            $flow->fn(static function (mixed $carry) use ($argument): mixed {
                if (!\is_callable($carry)) {
                    throw new \RuntimeException('Cannot apply more arguments to a non-function value.');
                }

                return $carry($argument);
            });
        }

        $flow->fn(static function (mixed $carry) use (&$result): mixed {
            $result = $carry;

            return $carry;
        });

        $flow(new Ip($appliedArguments[0]));
        $flow->await();

        return $result;
    }
}
