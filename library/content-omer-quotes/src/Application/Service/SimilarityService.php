<?php

declare(strict_types=1);

namespace App\Application\Service;

final class SimilarityService
{
    /**
     * @param array<string, float> $a
     * @param array<string, float> $b
     */
    public function cosine(array $a, array $b): float
    {
        $dot = 0.0;
        foreach ($a as $token => $weight) {
            $dot += $weight * ($b[$token] ?? 0.0);
        }

        $normA = sqrt(array_sum(array_map(static fn (float $v): float => $v * $v, $a)));
        $normB = sqrt(array_sum(array_map(static fn (float $v): float => $v * $v, $b)));
        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        return $dot / ($normA * $normB);
    }
}
