<?php

declare(strict_types=1);

namespace App\TokenFlow;

final class TokenEstimator
{
    /**
     * Simple deterministic heuristic — not a real tokenizer.
     */
    public function estimate(string $text): int
    {
        $length = mb_strlen($text);
        if ($length === 0) {
            return 0;
        }

        $base = (int) ceil($length / 4);
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $wordBonus = $words !== false ? (int) floor(count($words) / 8) : 0;

        return $base + $wordBonus;
    }
}
