<?php

declare(strict_types=1);

namespace App\Domain\Primitive;

use App\Domain\Execution\LambdaExecutionContext;

/**
 * Executes a primitive by name. Composio is one possible backend implementation.
 */
interface PrimitiveInvokerInterface
{
    public function invoke(PrimitiveInvocation $invocation, LambdaExecutionContext $context): mixed;
}
