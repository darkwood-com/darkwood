<?php

namespace App\Agent\Runtime;

final readonly class AgentOutput
{
    /**
     * @param array<string, mixed> $payload
     * @param list<string> $artifacts
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public array $payload,
        public array $artifacts = [],
        public array $metadata = [],
    ) {
    }
}
