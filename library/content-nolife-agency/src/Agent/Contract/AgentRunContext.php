<?php

namespace App\Agent\Contract;

final readonly class AgentRunContext
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        private string $runId,
        private string $requester,
        private ?string $traceId,
        private array $context = [],
    ) {
    }

    public function runId(): string
    {
        return $this->runId;
    }

    public function requester(): string
    {
        return $this->requester;
    }

    public function traceId(): ?string
    {
        return $this->traceId;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
