<?php

declare(strict_types=1);

namespace App\Study\Agent;

use App\Study\Payload;
use Flow\JobInterface;

/**
 * Deterministic next-action rules for a fake agent. The agent itself is not an LLM.
 *
 * @implements JobInterface<Payload, Payload>
 */
final class RuleAgentJob implements JobInterface
{
    public function __invoke($data): mixed
    {
        $payload = $data instanceof Payload ? $data : Payload::fromInput($data);
        $state = self::state($payload->normalized);
        $start = hrtime(true);

        $decision = 'continue';
        if ($state['remaining'] <= 0) {
            $decision = 'stop';
        } elseif ('error' === $state['last_result'] && $state['retries'] >= 2) {
            $decision = 'escalate';
        } elseif ('error' === $state['last_result']) {
            $decision = 'retry';
        }

        $payload->decision = $decision;
        $payload->confidence = null;
        $payload->alternatives = [];
        $payload->answers = [];
        $payload->latencyMs = (hrtime(true) - $start) / 1_000_000;
        $payload->source = 'rule';
        $payload->job = self::class;
        $payload->error = null;
        $payload->context = $state;

        return $payload;
    }

    /**
     * @return array{objective: string, step: int, last_result: string, remaining: int, retries: int, note: string}
     */
    public static function state(mixed $normalized): array
    {
        if (!\is_array($normalized)) {
            throw new \InvalidArgumentException('Agent state must be an array.');
        }

        return [
            'objective' => (string) ($normalized['objective'] ?? ''),
            'step' => (int) ($normalized['step'] ?? 0),
            'last_result' => (string) ($normalized['last_result'] ?? 'ok'),
            'remaining' => (int) ($normalized['remaining'] ?? 0),
            'retries' => (int) ($normalized['retries'] ?? 0),
            'note' => (string) ($normalized['note'] ?? ''),
        ];
    }
}
