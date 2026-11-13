<?php

namespace App\Agent\Registry;

use App\Agent\Contract\AgentInterface;
use App\Agent\Contract\AgentRegistryInterface;

final class ConfiguredAgentRegistry extends InMemoryAgentRegistry implements AgentRegistryInterface
{
    /**
     * @param iterable<AgentInterface> $agents
     */
    public function __construct(iterable $agents)
    {
        parent::__construct($agents);
    }
}
