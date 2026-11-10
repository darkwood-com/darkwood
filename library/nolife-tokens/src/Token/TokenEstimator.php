<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Estimates tokens without a provider tokenizer.
 *
 * Uses bytes / 4 (same heuristic RTK documents). Percentages between raw and
 * optimized are meaningful; absolute token counts are approximate — never treat
 * them as exact billed tokens.
 */
final class TokenEstimator
{
    public function measure(string $content): ContextMetrics
    {
        $bytes = strlen($content);
        $characters = mb_strlen($content);
        $lines = $content === '' ? 0 : substr_count($content, "\n") + 1;

        return new ContextMetrics(
            bytes: $bytes,
            characters: $characters,
            lines: $lines,
            estimatedTokens: (int) ceil($bytes / 4),
        );
    }
}
