<?php

declare(strict_types=1);

namespace App\Application\Training;

use App\Domain\CellularAutomaton\Frame;

final class UnsupervisedFitnessScorer
{
    /**
     * @param list<Frame> $frames
     */
    public function score(array $frames, int $size): float
    {
        if ($frames === [] || $size <= 0) {
            return 0.0;
        }

        $viability = $this->nonCollapseScore($frames, $size);
        if ($viability <= 0.0) {
            return 0.0;
        }

        $score = $viability * (
            (0.30 * $this->motionScore($frames, $size))
            + (0.30 * $this->edgeDensityScore($this->lastMatrix($frames), $size))
            + (0.15 * $this->symmetryScore($this->lastMatrix($frames), $size))
            + (0.15 * $this->temporalVariationScore($frames))
            + (0.10 * min(1.0, \count($frames) / 20.0))
        );

        return max(0.0, min(1.0, $score));
    }

    /**
     * @param list<Frame> $frames
     */
    private function nonCollapseScore(array $frames, int $size): float
    {
        $window = \array_slice($frames, -8);
        $ratios = [];

        foreach ($window as $frame) {
            $ratios[] = $this->aliveRatio($frame->grid()->toBinaryMatrix(), $size);
        }

        $mean = \array_sum($ratios) / max(1, \count($ratios));
        if ($mean <= 0.0 || $mean >= 1.0) {
            return 0.0;
        }

        $low = 0.02;
        $high = 0.98;
        if ($mean < $low) {
            return $mean / $low;
        }

        if ($mean > $high) {
            return (1.0 - $mean) / (1.0 - $high);
        }

        return 1.0;
    }

    /**
     * @param list<Frame> $frames
     */
    private function motionScore(array $frames, int $size): float
    {
        if (\count($frames) < 2) {
            return 0.0;
        }

        $changes = 0;
        $comparisons = 0;
        for ($i = 1; $i < \count($frames); ++$i) {
            $previous = $frames[$i - 1]->grid()->toBinaryMatrix();
            $current = $frames[$i]->grid()->toBinaryMatrix();

            for ($y = 0; $y < $size; ++$y) {
                for ($x = 0; $x < $size; ++$x) {
                    if (($previous[$y][$x] ?? 0) !== ($current[$y][$x] ?? 0)) {
                        ++$changes;
                    }
                    ++$comparisons;
                }
            }
        }

        return $changes / max(1, $comparisons);
    }

    /**
     * @param list<list<int>> $matrix
     */
    private function edgeDensityScore(array $matrix, int $size): float
    {
        $edges = 0;
        $possible = max(1, 2 * $size * ($size - 1));

        for ($y = 0; $y < $size; ++$y) {
            for ($x = 0; $x < $size; ++$x) {
                if ($x + 1 < $size && (($matrix[$y][$x] ?? 0) !== ($matrix[$y][$x + 1] ?? 0))) {
                    ++$edges;
                }
                if ($y + 1 < $size && (($matrix[$y][$x] ?? 0) !== ($matrix[$y + 1][$x] ?? 0))) {
                    ++$edges;
                }
            }
        }

        return $edges / $possible;
    }

    /**
     * @param list<list<int>> $matrix
     */
    private function symmetryScore(array $matrix, int $size): float
    {
        $verticalMatches = 0;
        $horizontalMatches = 0;
        $total = max(1, $size * $size);

        for ($y = 0; $y < $size; ++$y) {
            for ($x = 0; $x < $size; ++$x) {
                if (($matrix[$y][$x] ?? 0) === ($matrix[$y][$size - 1 - $x] ?? 0)) {
                    ++$verticalMatches;
                }
                if (($matrix[$y][$x] ?? 0) === ($matrix[$size - 1 - $y][$x] ?? 0)) {
                    ++$horizontalMatches;
                }
            }
        }

        return (($verticalMatches / $total) + ($horizontalMatches / $total)) / 2.0;
    }

    /**
     * @param list<Frame> $frames
     */
    private function temporalVariationScore(array $frames): float
    {
        $counts = [];
        foreach ($frames as $frame) {
            $counts[] = $this->aliveCount($frame->grid()->toBinaryMatrix());
        }

        $mean = \array_sum($counts) / max(1, \count($counts));
        if ($mean <= 0.0) {
            return 0.0;
        }

        $variance = 0.0;
        foreach ($counts as $count) {
            $variance += ($count - $mean) ** 2;
        }
        $stdDev = sqrt($variance / max(1, \count($counts)));
        $coefficient = $stdDev / max(1.0, $mean);

        return max(0.0, 1.0 - min(1.0, abs($coefficient - 0.25) / 0.75));
    }

    /**
     * @param list<Frame> $frames
     *
     * @return list<list<int>>
     */
    private function lastMatrix(array $frames): array
    {
        return $frames[\array_key_last($frames)]->grid()->toBinaryMatrix();
    }

    /**
     * @param list<list<int>> $matrix
     */
    private function aliveRatio(array $matrix, int $size): float
    {
        return $this->aliveCount($matrix) / max(1, $size * $size);
    }

    /**
     * @param list<list<int>> $matrix
     */
    private function aliveCount(array $matrix): int
    {
        $alive = 0;
        foreach ($matrix as $row) {
            $alive += \array_sum($row);
        }

        return $alive;
    }
}
