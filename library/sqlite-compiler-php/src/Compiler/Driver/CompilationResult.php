<?php

declare(strict_types=1);

namespace App\Compiler\Driver;

use App\Compiler\Common\Diagnostic;

final class CompilationResult
{
    /**
     * @param list<Diagnostic> $diagnostics
     * @param array<string, float> $timingsMs
     * @param array<string, int|string> $metrics
     */
    public function __construct(
        public readonly bool $success,
        public readonly ?string $assembly = null,
        public readonly array $diagnostics = [],
        public readonly array $timingsMs = [],
        public readonly array $metrics = [],
    ) {
    }
}
