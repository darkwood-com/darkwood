<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Visible optimized text plus material to store as reversible references.
 */
final class OptimizeResult
{
    /**
     * @param list<array{reason: string, content: string, marker_placeholder?: string}> $omissions
     */
    public function __construct(
        public readonly string $visible,
        public readonly array $omissions = [],
    ) {}
}
