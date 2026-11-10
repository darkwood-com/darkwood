<?php

declare(strict_types=1);

namespace App\Study\Risk;

use App\Study\Payload;
use Flow\JobInterface;

/**
 * Toy threshold rules. Not a production fraud system.
 *
 * @implements JobInterface<Payload, Payload>
 */
final class RuleRiskJob implements JobInterface
{
    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $tx = self::tx($payload->normalized);
        $start = hrtime(true);

        $amount = (float) $tx['amount'];
        $unusual = (bool) $tx['unusual'];
        $riskyCountry = \in_array((string) $tx['country'], ['NG', 'RU', 'XX'], true);

        if ($amount >= 5000 && $unusual) {
            $decision = 'block';
        } elseif ($amount >= 1000 || $unusual || $riskyCountry) {
            $decision = 'review';
        } else {
            $decision = 'allow';
        }

        $payload->decision = $decision;
        $payload->confidence = null;
        $payload->alternatives = [];
        $payload->answers = [];
        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = 'rule';
        $payload->job = self::class;
        $payload->error = null;
        $payload->context = $tx;

        return $payload;
    }

    /**
     * @return array{amount: float, country: string, device: string, previous: string, unusual: bool}
     */
    public static function tx(mixed $normalized): array
    {
        if (!\is_array($normalized)) {
            throw new \InvalidArgumentException('Risk input must be an array.');
        }

        return [
            'amount' => (float) ($normalized['amount'] ?? 0),
            'country' => (string) ($normalized['country'] ?? ''),
            'device' => (string) ($normalized['device'] ?? ''),
            'previous' => (string) ($normalized['previous'] ?? ''),
            'unusual' => (bool) ($normalized['unusual'] ?? false),
        ];
    }
}
