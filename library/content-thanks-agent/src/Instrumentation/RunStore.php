<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Model\AgentId;
use App\Model\AgentResult;

/**
 * Shared gather bag for scatter/fan-in after concurrent agent IPs complete.
 */
final class RunStore
{
    /** @var array<string, AgentResult> */
    private array $results = [];

    public function reset(): void
    {
        $this->results = [];
    }

    public function put(AgentResult $result): void
    {
        $this->results[$result->agentId->value] = $result;
    }

    public function get(AgentId $agentId): ?AgentResult
    {
        return $this->results[$agentId->value] ?? null;
    }

    /**
     * @return list<AgentResult>
     */
    public function all(): array
    {
        return array_values($this->results);
    }

    public function count(): int
    {
        return count($this->results);
    }
}
