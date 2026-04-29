<?php

namespace App\Agent\Runtime;

final readonly class AgentInput
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public array $payload,
        public array $metadata = [],
    ) {
    }
}
