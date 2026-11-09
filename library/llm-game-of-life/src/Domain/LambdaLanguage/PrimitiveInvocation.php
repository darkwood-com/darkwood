<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

final readonly class PrimitiveInvocation
{
    /**
     * @param array<string,mixed> $arguments
     */
    public function __construct(
        public string $primitiveName,
        public array $arguments,
    ) {
    }
}
