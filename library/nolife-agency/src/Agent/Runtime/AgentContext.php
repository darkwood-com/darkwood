<?php

namespace App\Agent\Runtime;

use App\Agent\Contract\AgentContextInterface;

final readonly class AgentContext implements AgentContextInterface
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $runId,
        private string $requester,
        private ?string $projectKey = null,
        private ?string $traceId = null,
        private array $metadata = [],
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

    public function projectKey(): ?string
    {
        return $this->projectKey;
    }

    public function traceId(): ?string
    {
        return $this->traceId;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
