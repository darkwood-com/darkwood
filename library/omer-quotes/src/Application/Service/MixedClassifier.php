<?php

declare(strict_types=1);

namespace App\Application\Service;

final class MixedClassifier
{
    /**
     * @param array<string, array{label: string, scores: array<string, float>}> $base
     * @param array<string, array{label: string, scores: array<string, float>}> $wordClass
     * @return array<string, array{label: string, scores: array<string, float>}>
     */
    public function combine(array $base, array $wordClass, float $baseWeight = 0.7): array
    {
        $out = [];
        foreach ($base as $repoId => $baseResult) {
            $scores = [];
            foreach ($baseResult['scores'] as $label => $score) {
                $scores[$label] = $baseWeight * $score;
            }
            foreach (($wordClass[$repoId]['scores'] ?? []) as $label => $score) {
                $scores[$label] = ($scores[$label] ?? 0.0) + (1 - $baseWeight) * $score;
            }
            arsort($scores);
            $out[$repoId] = ['label' => (string) array_key_first($scores), 'scores' => $scores];
        }

        return $out;
    }
}
