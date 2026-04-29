<?php

namespace App\Agent\Definition;

final readonly class AgentOutputSchema
{
    /**
     * @param array<string, mixed> $schema
     */
    public function __construct(
        public array $schema,
    ) {
    }
}
