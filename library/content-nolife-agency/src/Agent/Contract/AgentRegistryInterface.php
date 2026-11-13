<?php

namespace App\Agent\Contract;

use App\Agent\Definition\AgentDefinition;

interface AgentRegistryInterface
{
    /**
     * @return list<AgentDefinition>
     */
    public function all(): array;

    public function get(string $agentKey): AgentInterface;

    public function has(string $agentKey): bool;
}
