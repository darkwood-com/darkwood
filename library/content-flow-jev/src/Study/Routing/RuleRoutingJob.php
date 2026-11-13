<?php

declare(strict_types=1);

namespace App\Study\Routing;

use App\Study\Payload;
use Flow\JobInterface;

/**
 * @implements JobInterface<Payload, Payload>
 */
final class RuleRoutingJob implements JobInterface
{
    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $text = strtolower((string) $payload->normalized);
        $start = hrtime(true);

        $decision = 'technical_support';
        if (self::matches($text, ['fraud', 'stolen', 'unauthorized', 'not me', 'hacked', 'takeover'])) {
            $decision = 'fraud';
        } elseif (self::matches($text, ['charge', 'charged', 'invoice', 'refund', 'billing', 'payment', 'card', 'payout'])) {
            $decision = 'billing';
        } elseif (self::matches($text, ['price', 'pricing', 'plan', 'upgrade', 'buy', 'sales', 'quote'])) {
            $decision = 'sales';
        } elseif (self::matches($text, ['crash', 'bug', 'error', 'down', 'outage', '500', 'api', 'integration'])) {
            $decision = 'technical_support';
        }

        $payload->decision = $decision;
        $payload->confidence = null;
        $payload->alternatives = [];
        $payload->answers = [];
        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = 'rule';
        $payload->job = self::class;
        $payload->error = null;

        return $payload;
    }

    /**
     * @param list<string> $needles
     */
    private static function matches(string $text, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($text, $needle)) {
                return true;
            }
        }

        return false;
    }
}
