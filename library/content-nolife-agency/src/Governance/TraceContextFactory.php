<?php

namespace App\Governance;

final class TraceContextFactory
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function create(string $runId, string $agentName, array $payload = []): array
    {
        return [
            'run_id' => $runId,
            'agent_name' => $agentName,
            'payload' => $payload,
            'created_at' => (new \DateTimeImmutable())->format(\DATE_ATOM),
        ];
    }
}
