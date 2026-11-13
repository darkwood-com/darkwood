<?php

declare(strict_types=1);

namespace App\Domain\Capability;

use App\Domain\Lambda\LambdaExpression;

/**
 * Central domain capability: execution is lambda-first (programs are lambda expressions).
 */
final readonly class LambdaCapability
{
    public function __construct(private ?LambdaExpression $defaultProgram = null)
    {
    }

    public function defaultProgram(): ?LambdaExpression
    {
        return $this->defaultProgram;
    }
}
