<?php

declare(strict_types=1);

namespace App\Study\Risk;

use App\Study\JevInvoke;
use App\Study\Payload;
use Flow\JobInterface;
use Symfony\AI\Platform\PlatformInterface;

/**
 * @implements JobInterface<Payload, Payload>
 */
final class JevRiskJob implements JobInterface
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $source,
    ) {
    }

    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $tx = RuleRiskJob::tx($payload->normalized);
        $start = hrtime(true);

        try {
            $result = JevInvoke::call($this->platform, $tx, self::questions());
            $payload->decision = $result->getChoice('action');
            $payload->confidence = $result->getConfidence('action');
            $payload->alternatives = $result->getProbabilities('action');
            $payload->answers = $result->toArray();
            $payload->error = null;
        } catch (\Throwable $e) {
            $payload->decision = null;
            $payload->error = $e->getMessage();
        }

        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = $this->source;
        $payload->job = self::class;
        $payload->context = $tx;

        return $payload;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function questions(): array
    {
        return [
            'action' => [
                'type' => 'choice',
                'instructions' => 'Given this toy payment context, should the payment be allowed, sent to manual review, or blocked? This is a demonstration, not a real fraud engine.',
                'criteria' => [
                    'allow' => 'Looks like a normal customer payment.',
                    'review' => 'Some risk signals; a human should look.',
                    'block' => 'Strong signs of abuse or fraud.',
                ],
            ],
            'risk' => [
                'type' => 'score',
                'instructions' => 'How risky does this payment look?',
                'criteria' => [
                    'Ordinary, low concern.',
                    'Some unusual signals.',
                    'High concern.',
                ],
            ],
        ];
    }
}
