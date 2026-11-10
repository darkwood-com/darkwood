<?php

namespace App\Agent\Runtime;

final class AgentRun
{
    public function __construct(
        public readonly AgentRunId $id,
        public readonly string $agentKey,
        public AgentExecutionStatus $status,
        public readonly \DateTimeImmutable $startedAt,
        public ?\DateTimeImmutable $finishedAt = null,
        public ?string $traceId = null,
    ) {
    }
}
