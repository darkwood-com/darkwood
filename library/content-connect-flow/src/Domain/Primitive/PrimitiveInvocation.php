<?php

declare(strict_types=1);

namespace App\Domain\Primitive;

/**
 * Request to run a named primitive (e.g. Composio-backed tool) with optional arguments.
 *
 * @phpstan-type Arguments array<string, mixed>
 */
final readonly class PrimitiveInvocation
{
    /**
     * @param array<string, mixed> $arguments
     */
    public function __construct(
        public string $primitiveName,
        public array $arguments = [],
    ) {
    }
}
