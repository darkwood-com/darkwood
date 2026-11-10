<?php

declare(strict_types=1);

namespace App\Token;

/**
 * Checks that planted signals still appear in optimized context.
 */
final class SignalEvaluator
{
    /**
     * @param list<string> $expectedSignals
     *
     * @return list<string> missing signals
     */
    public function missing(string $optimized, array $expectedSignals): array
    {
        $missing = [];
        foreach ($expectedSignals as $signal) {
            if ($signal === '') {
                continue;
            }
            if (!str_contains($optimized, $signal)) {
                $missing[] = $signal;
            }
        }

        return $missing;
    }

    /**
     * @param list<string> $expectedSignals
     * @param list<string> $missingSignals
     *
     * @return list<string>
     */
    public function retained(array $expectedSignals, array $missingSignals): array
    {
        return array_values(array_diff($expectedSignals, $missingSignals));
    }
}
