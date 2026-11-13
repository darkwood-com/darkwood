<?php

declare(strict_types=1);

namespace App\Infrastructure\ExpressionLanguage;

use App\Domain\Execution\LambdaExecutionContext;
use App\Infrastructure\Flow\LambdaFlowRuntime;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Host integration: adds lambda() which delegates to the Flow runtime.
 */
final class LambdaExpressionLanguageFactory
{
    public function __construct(
        private LambdaFlowRuntime $runtime,
    ) {
    }

    public function create(?LambdaExecutionContext $context = null): ExpressionLanguage
    {
        $language = new ExpressionLanguage();
        $ctx = $context ?? new LambdaExecutionContext();

        $language->register(
            'lambda',
            static fn ($expressionCode, ...$argumentsCode): string => 'null',
            function (array $arguments, $expression, ...$lambdaArguments) use ($ctx): mixed {
                if (!\is_string($expression) || $expression === '') {
                    throw new \InvalidArgumentException('lambda() expects a non-empty string expression.');
                }

                return $this->runtime->evaluateThroughFlow($expression, $ctx, $lambdaArguments);
            }
        );

        return $language;
    }
}
