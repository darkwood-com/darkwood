<?php

namespace App\Agent\Definition;

final readonly class AgentDefinition
{
    /**
     * @param list<string> $responsibilities
     * @param list<AgentCapability> $capabilities
     * @param list<AgentToolDefinition> $tools
     */
    public function __construct(
        public string $key,
        public string $name,
        public string $role,
        public string $description,
        public array $responsibilities,
        public array $capabilities = [],
        public array $tools = [],
        public ?AgentInputSchema $inputSchema = null,
        public ?AgentOutputSchema $outputSchema = null,
    ) {
    }
}
