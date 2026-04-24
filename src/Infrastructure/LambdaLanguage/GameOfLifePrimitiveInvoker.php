<?php

declare(strict_types=1);

namespace App\Infrastructure\LambdaLanguage;

use App\Domain\LambdaLanguage\LambdaExecutionContext;
use App\Domain\LambdaLanguage\PrimitiveInvocation;
use App\Domain\LambdaLanguage\PrimitiveInvokerInterface;

final class GameOfLifePrimitiveInvoker implements PrimitiveInvokerInterface
{
    public function invoke(PrimitiveInvocation $invocation, LambdaExecutionContext $context): mixed
    {
        return match ($invocation->primitiveName) {
            'birth' => $this->invokeBirth($invocation, $context),
            'kill' => $this->invokeKill($invocation, $context),
            'paint' => $this->invokePaint($invocation, $context),
            'next' => $this->invokeNext($context),
            default => throw new \InvalidArgumentException(\sprintf('Unknown primitive "%s".', $invocation->primitiveName)),
        };
    }

    private function invokeBirth(PrimitiveInvocation $invocation, LambdaExecutionContext $context): null
    {
        $context->state->birth(
            $this->readInt($invocation->arguments, 'x'),
            $this->readInt($invocation->arguments, 'y'),
        );

        return null;
    }

    private function invokeKill(PrimitiveInvocation $invocation, LambdaExecutionContext $context): null
    {
        $context->state->kill(
            $this->readInt($invocation->arguments, 'x'),
            $this->readInt($invocation->arguments, 'y'),
        );

        return null;
    }

    private function invokePaint(PrimitiveInvocation $invocation, LambdaExecutionContext $context): null
    {
        $color = $invocation->arguments['color'] ?? null;
        if (!\is_string($color)) {
            throw new \InvalidArgumentException('paint payload must contain a string color.');
        }

        $context->state->paint(
            $this->readInt($invocation->arguments, 'x'),
            $this->readInt($invocation->arguments, 'y'),
            $color,
        );

        return null;
    }

    private function invokeNext(LambdaExecutionContext $context): null
    {
        $context->state->next();

        return null;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function readInt(array $payload, string $key): int
    {
        $value = $payload[$key] ?? null;
        if (!\is_int($value)) {
            throw new \InvalidArgumentException(\sprintf('Payload key "%s" must be an integer.', $key));
        }

        return $value;
    }
}
