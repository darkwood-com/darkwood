<?php

declare(strict_types=1);

namespace App\Model;

final readonly class AgentPacket
{
    /**
     * @param array<string, mixed> $context
     */
    public function __construct(
        public AgentId $agentId,
        public string $runId,
        public array $context = [],
        public bool $simulateError = false,
    ) {}
}
