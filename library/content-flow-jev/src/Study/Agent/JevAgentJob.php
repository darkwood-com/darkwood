<?php

declare(strict_types=1);

namespace App\Study\Agent;

use App\Study\JevInvoke;
use App\Study\Payload;
use Flow\JobInterface;
use Symfony\AI\Platform\PlatformInterface;

/**
 * @implements JobInterface<Payload, Payload>
 */
final class JevAgentJob implements JobInterface
{
    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly string $source,
    ) {
    }

    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $state = RuleAgentJob::state($payload->normalized);
        $start = hrtime(true);

        try {
            $result = JevInvoke::call($this->platform, $state, self::questions());
            $payload->decision = $result->getChoice('next');
            $payload->confidence = $result->getConfidence('next');
            $payload->alternatives = $result->getProbabilities('next');
            $payload->answers = $result->toArray();
            $payload->error = null;
        } catch (\Throwable $e) {
            $payload->decision = null;
            $payload->error = $e->getMessage();
        }

        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = $this->source;
        $payload->job = self::class;
        $payload->context = $state;

        return $payload;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function questions(): array
    {
        return [
            'next' => [
                'type' => 'choice',
                'instructions' => 'A fake software agent already executed the last step. Choose the next control action. Do not generate prose or a plan.',
                'criteria' => [
                    'continue' => 'Work remains and the last step succeeded.',
                    'retry' => 'The last step failed and is worth trying again.',
                    'stop' => 'The objective is done, or further work is pointless.',
                    'escalate' => 'The agent is stuck; a human should take over.',
                ],
            ],
        ];
    }
}
