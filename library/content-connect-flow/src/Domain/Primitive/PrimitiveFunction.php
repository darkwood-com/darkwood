<?php

declare(strict_types=1);

namespace App\Domain\Primitive;

use App\Domain\Execution\LambdaExecutionContext;

/**
 * Callable primitive bound into a lambda as a curried argument.
 */
final readonly class PrimitiveFunction
{
    public function __construct(
        private string $primitiveName,
        private PrimitiveInvokerInterface $invoker,
        private LambdaExecutionContext $context,
    ) {
    }

    public function __invoke(mixed $arg = null): mixed
    {
        $arguments = match (true) {
            \func_num_args() === 0 => [],
            \is_array($arg) => $arg,
            default => ['value' => $arg],
        };

        return $this->invoker->invoke(
            new PrimitiveInvocation($this->primitiveName, $arguments),
            $this->context,
        );
    }
}
