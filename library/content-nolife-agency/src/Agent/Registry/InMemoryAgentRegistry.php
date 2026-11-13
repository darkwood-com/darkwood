<?php

namespace App\Agent\Registry;

use App\Agent\Contract\AgentInterface;
use App\Agent\Contract\AgentRegistryInterface;
use App\Agent\Definition\AgentDefinition;

class InMemoryAgentRegistry implements AgentRegistryInterface
{
    /** @var array<string, AgentInterface> */
    private array $agentsByKey = [];

    /**
     * @param iterable<AgentInterface> $agents
     */
    public function __construct(iterable $agents)
    {
        foreach ($agents as $agent) {
            $this->agentsByKey[$agent->definition()->key] = $agent;
        }
    }

    public function all(): array
    {
        return array_values(array_map(
            static fn (AgentInterface $agent): AgentDefinition => $agent->definition(),
            $this->agentsByKey
        ));
    }

    public function get(string $agentKey): AgentInterface
    {
        if (!$this->has($agentKey)) {
            throw new \InvalidArgumentException(sprintf('Unknown agent key "%s".', $agentKey));
        }

        return $this->agentsByKey[$agentKey];
    }

    public function has(string $agentKey): bool
    {
        return isset($this->agentsByKey[$agentKey]);
    }
}
