<?php

declare(strict_types=1);

namespace App\Application\Service;

final class ClassificationService
{
    /**
     * @param array<string, array<string, float>> $vectors
     * @param array<string, string> $labelsByRepo
     * @return array<string, array{label: string, scores: array<string, float>}>
     */
    public function classify(array $vectors, array $labelsByRepo): array
    {
        $tokenTotals = [];
        $classDocCount = [];
        $vocabulary = [];

        foreach ($labelsByRepo as $repoId => $label) {
            $classDocCount[$label] = ($classDocCount[$label] ?? 0) + 1;
            foreach (($vectors[$repoId] ?? []) as $token => $weight) {
                $tokenTotals[$label][$token] = ($tokenTotals[$label][$token] ?? 0.0) + $weight;
                $vocabulary[$token] = true;
            }
        }

        $vocabSize = max(count($vocabulary), 1);
        $totalDocs = max(array_sum($classDocCount), 1);
        $results = [];

        foreach ($vectors as $repoId => $vector) {
            $scores = [];
            foreach ($classDocCount as $label => $count) {
                $logProb = log($count / $totalDocs);
                $sumClass = array_sum($tokenTotals[$label] ?? []);
                foreach ($vector as $token => $weight) {
                    $cond = (($tokenTotals[$label][$token] ?? 0.0) + 1.0) / ($sumClass + $vocabSize);
                    $logProb += $weight * log($cond);
                }
                $scores[$label] = $logProb;
            }

            arsort($scores);
            $label = array_key_first($scores) ?? 'unknown';
            $results[$repoId] = ['label' => (string) $label, 'scores' => $scores];
        }

        return $results;
    }

    public function heuristicLabel(string $repoId, string $fullName): string
    {
        $haystack = strtolower($repoId . ' ' . $fullName);
        if (str_contains($haystack, 'symfony') || str_contains($haystack, 'php')) {
            return 'web';
        }
        if (str_contains($haystack, 'game') || str_contains($haystack, 'jeu')) {
            return 'game';
        }

        return 'misc';
    }
}
