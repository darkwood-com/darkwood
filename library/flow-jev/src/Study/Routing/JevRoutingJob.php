<?php

declare(strict_types=1);

namespace App\Study\Routing;

use App\Study\JevInvoke;
use App\Study\Payload;
use Flow\JobInterface;
use Symfony\AI\Platform\PlatformInterface;

/**
 * @implements JobInterface<Payload, Payload>
 */
final class JevRoutingJob implements JobInterface
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $source,
    ) {
    }

    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $start = hrtime(true);

        try {
            $result = JevInvoke::call($this->platform, (string) $payload->normalized, self::questions());
            $payload->decision = $result->getChoice('department');
            $payload->confidence = $result->getConfidence('department');
            $payload->alternatives = $result->getProbabilities('department');
            $payload->answers = $result->toArray();
            $payload->error = null;
        } catch (\Throwable $e) {
            $payload->decision = null;
            $payload->error = $e->getMessage();
        }

        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = $this->source;
        $payload->job = self::class;

        return $payload;
    }

    /**
     * One request, two independent questions — not one Flow stage per question.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function questions(): array
    {
        return [
            'department' => [
                'type' => 'choice',
                'instructions' => 'Which team should handle this customer message?',
                'criteria' => [
                    'billing' => 'Payments, charges, invoices, refunds, subscriptions, payouts.',
                    'technical_support' => 'Bugs, crashes, outages, integrations, or the product not working.',
                    'sales' => 'Pricing, plans, buying, upgrades, quotes.',
                    'fraud' => 'Unauthorized charges, stolen card, account takeover, "this was not me".',
                ],
            ],
            'is_urgent' => [
                'type' => 'noul',
                'instructions' => 'Does this message convey urgency or time-sensitivity?',
            ],
        ];
    }
}
