<?php

namespace App\Agent\Definition;

final readonly class AgentCapability
{
    public function __construct(
        public string $name,
    ) {
    }
}
