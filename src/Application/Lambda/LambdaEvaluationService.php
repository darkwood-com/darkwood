<?php

declare(strict_types=1);

namespace App\Application\Lambda;

use App\Domain\Execution\LambdaExecutionContext;
use App\Domain\Primitive\PrimitiveFunction;
use App\Domain\Primitive\PrimitiveInvokerInterface;
use Flow\Job\LambdaJob;

/**
 * Small orchestration layer around Flow's LambdaJob.
 *
 * Registered primitives are injected as the leading curried arguments, in the
 * configured order, and user arguments are applied afterwards.
 */
final class LambdaEvaluationService
{
    /**
     * @param list<string> $registeredPrimitives
     */
    public function __construct(
        private ?PrimitiveInvokerInterface $invoker,
        private array $registeredPrimitives,
    ) {
    }

    /**
     * @param list<mixed> $arguments
     */
    public function evaluate(string $source, LambdaExecutionContext $context, array $arguments = []): mixed
    {
        $source = $this->normalizeSource($source);
        $appliedArguments = $this->appliedArguments($context, $arguments);

        if ($appliedArguments === []) {
            throw new \InvalidArgumentException(
                'At least one lambda argument or configured primitive binding is required.'
            );
        }

        $job = new LambdaJob($source);
        $result = null;

        foreach ($appliedArguments as $index => $argument) {
            if ($index === 0) {
                $result = $job($argument);

                continue;
            }

            if (!\is_callable($result)) {
                throw new \RuntimeException('Cannot apply more arguments to a non-function value.');
            }

            $result = $result($argument);
        }

        return $this->finalizeResult($result);
    }

    public function normalizeSource(string $source): string
    {
        $normalized = $source;

        foreach ($this->registeredPrimitives as $primitiveName) {
            $identifier = $this->lambdaIdentifierForPrimitive($primitiveName);
            $normalized = preg_replace(
                '/(?<![a-zA-Z0-9])' . preg_quote($primitiveName, '/') . '(?![a-zA-Z0-9])/',
                $identifier,
                $normalized,
            ) ?? $normalized;
        }

        return $normalized;
    }

    /**
     * @param list<mixed> $arguments
     *
     * @return list<mixed>
     */
    public function appliedArguments(LambdaExecutionContext $context, array $arguments = []): array
    {
        return [
            ...$this->primitiveBindings($context),
            ...array_values($arguments),
        ];
    }

    public function finalizeResult(mixed $result): mixed
    {
        if ($result instanceof PrimitiveFunction) {
            return $result();
        }

        return $result;
    }

    /**
     * @return list<PrimitiveFunction>
     */
    private function primitiveBindings(LambdaExecutionContext $context): array
    {
        if ($this->registeredPrimitives === []) {
            return [];
        }

        if ($this->invoker === null) {
            throw new \RuntimeException('Primitive bindings are configured but no primitive invoker is available.');
        }

        return array_map(
            fn (string $primitiveName): PrimitiveFunction => new PrimitiveFunction(
                $primitiveName,
                $this->invoker,
                $context,
            ),
            $this->registeredPrimitives,
        );
    }

    private function lambdaIdentifierForPrimitive(string $primitiveName): string
    {
        $identifier = preg_replace('/[^a-zA-Z0-9]+/', '', $primitiveName) ?? '';
        if ($identifier === '') {
            throw new \InvalidArgumentException(sprintf(
                'Primitive name "%s" cannot be normalized to a lambda identifier.',
                $primitiveName,
            ));
        }

        return $identifier;
    }
}
