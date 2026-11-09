<?php

declare(strict_types=1);

namespace App\Domain\Lambda;

/**
 * First-class description of a lambda program (source text).
 */
final readonly class LambdaExpression
{
    public function __construct(private string $source)
    {
    }

    public function source(): string
    {
        return $this->source;
    }
}
