<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

use Flow\Job\LambdaJob;

final class LambdaEvaluationService
{
    /**
     * @param list<string> $registeredPrimitives
     */
    public function __construct(
        private PrimitiveInvokerInterface $invoker,
        private array $registeredPrimitives,
    ) {
    }

    /**
     * @param list<mixed> $arguments
     */
    public function evaluate(
        string $source,
        LambdaExecutionContext $context,
        array $arguments = [],
        ?array $primitiveNames = null,
    ): mixed
    {
        $primitiveNames ??= $this->registeredPrimitives;
        $source = $this->normalizeSource($source, $primitiveNames);
        $appliedArguments = $this->appliedArguments($context, $arguments, $primitiveNames);

        if ($appliedArguments === []) {
            throw new ProgramValidationException('At least one argument is required to execute lambda.');
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

    /**
     * @param list<mixed> $arguments
     *
     * @return list<mixed>
     */
    public function appliedArguments(
        LambdaExecutionContext $context,
        array $arguments = [],
        ?array $primitiveNames = null,
    ): array
    {
        $primitiveNames ??= $this->registeredPrimitives;

        return [
            ...$this->primitiveBindings($context, $primitiveNames),
            ...array_values($arguments),
        ];
    }

    public function normalizeSource(string $source, ?array $primitiveNames = null): string
    {
        $primitiveNames ??= $this->registeredPrimitives;
        $normalized = $source;

        foreach ($primitiveNames as $primitiveName) {
            $identifier = $this->lambdaIdentifierForPrimitive($primitiveName);
            $normalized = preg_replace(
                '/(?<![a-zA-Z0-9])' . preg_quote($primitiveName, '/') . '(?![a-zA-Z0-9])/',
                $identifier,
                $normalized,
            ) ?? $normalized;
        }

        return $normalized;
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
    private function primitiveBindings(LambdaExecutionContext $context, array $primitiveNames): array
    {
        return array_map(
            fn (string $primitiveName): PrimitiveFunction => new PrimitiveFunction(
                $primitiveName,
                $this->invoker,
                $context,
            ),
            $primitiveNames,
        );
    }

    private function lambdaIdentifierForPrimitive(string $primitiveName): string
    {
        $identifier = preg_replace('/[^a-zA-Z0-9]+/', '', $primitiveName) ?? '';
        if ($identifier === '') {
            throw new ProgramValidationException(\sprintf(
                'Primitive name "%s" cannot be normalized to a lambda identifier.',
                $primitiveName,
            ));
        }

        return $identifier;
    }
}
