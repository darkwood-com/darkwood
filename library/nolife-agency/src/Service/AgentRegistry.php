<?php

namespace App\Service;

use App\Agent\Contract\AgentInterface;

final readonly class AgentRegistry
{
    /** @var array<string, AgentInterface> */
    private array $indexedAgents;

    /**
     * @param iterable<AgentInterface> $agents
     */
    public function __construct(iterable $agents)
    {
        $indexed = [];

        foreach ($agents as $agent) {
            $indexed[$agent->name()] = $agent;
        }

        $this->indexedAgents = $indexed;
    }

    public function get(string $agentName): AgentInterface
    {
        if (!isset($this->indexedAgents[$agentName])) {
            throw new \InvalidArgumentException(sprintf('Unknown agent "%s".', $agentName));
        }

        return $this->indexedAgents[$agentName];
    }

    /**
     * @return list<string>
     */
    public function names(): array
    {
        return array_keys($this->indexedAgents);
    }
}
