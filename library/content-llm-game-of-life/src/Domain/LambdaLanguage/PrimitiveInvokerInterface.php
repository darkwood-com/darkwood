<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

interface PrimitiveInvokerInterface
{
    public function invoke(PrimitiveInvocation $invocation, LambdaExecutionContext $context): mixed;
}
