<?php

namespace App\Agent\Definition;

final readonly class AgentToolDefinition
{
    /**
     * @param array<string, mixed> $inputSchema
     */
    public function __construct(
        public string $name,
        public string $description,
        public array $inputSchema = [],
    ) {
    }
}
