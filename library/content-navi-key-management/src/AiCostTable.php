<?php

declare(strict_types=1);

namespace App;

/**
 * Explicit per-1M-token USD prices. Update this table when providers change rates.
 *
 * Prices checked against public Anthropic / OpenAI lists on 2026-09-19.
 * Missing metrics stay null — this is not a billing system.
 */
final class AiCostTable
{
    /**
     * @var array<string, array{input: float, output: float, cache_write: ?float, cache_read: ?float}>
     */
    private const PRICES = [
        'claude-sonnet-4-5' => ['input' => 3.00, 'output' => 15.00, 'cache_write' => 3.75, 'cache_read' => 0.30],
        'claude-sonnet-4-6' => ['input' => 3.00, 'output' => 15.00, 'cache_write' => 3.75, 'cache_read' => 0.30],
        'claude-haiku-4-5' => ['input' => 1.00, 'output' => 5.00, 'cache_write' => 1.25, 'cache_read' => 0.10],
        'gpt-4o-mini' => ['input' => 0.15, 'output' => 0.60, 'cache_write' => null, 'cache_read' => 0.075],
        'gpt-4.1-mini' => ['input' => 0.40, 'output' => 1.60, 'cache_write' => null, 'cache_read' => 0.10],
    ];

    /**
     * @return array{usd: ?float, priced: bool, rates: ?array{input: float, output: float, cache_write: ?float, cache_read: ?float}}
     */
    public function estimate(string $model, ?int $inputTokens, ?int $outputTokens, ?int $cacheWriteTokens, ?int $cacheReadTokens): array
    {
        $rates = $this->ratesFor($model);
        if ($rates === null) {
            return ['usd' => null, 'priced' => false, 'rates' => null];
        }

        $usd = 0.0;
        $usd += ($inputTokens ?? 0) * $rates['input'] / 1_000_000;
        $usd += ($outputTokens ?? 0) * $rates['output'] / 1_000_000;
        if ($rates['cache_write'] !== null) {
            $usd += ($cacheWriteTokens ?? 0) * $rates['cache_write'] / 1_000_000;
        }
        if ($rates['cache_read'] !== null) {
            $usd += ($cacheReadTokens ?? 0) * $rates['cache_read'] / 1_000_000;
        }

        return ['usd' => $usd, 'priced' => true, 'rates' => $rates];
    }

    /**
     * @return array{input: float, output: float, cache_write: ?float, cache_read: ?float}|null
     */
    public function ratesFor(string $model): ?array
    {
        if (isset(self::PRICES[$model])) {
            return self::PRICES[$model];
        }

        foreach (self::PRICES as $name => $rates) {
            if (str_starts_with($model, $name)) {
                return $rates;
            }
        }

        return null;
    }
}
