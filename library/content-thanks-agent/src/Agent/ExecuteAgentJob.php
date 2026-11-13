<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;
use App\Model\AgentResult;
use Flow\JobInterface;
use Generator;
use InvalidArgumentException;

/**
 * Routes an AgentPacket to the concrete agent job.
 *
 * @implements JobInterface<AgentPacket, AgentResult|Generator>
 */
final class ExecuteAgentJob implements JobInterface
{
    /**
     * @param array<string, AbstractAgent> $agents
     */
    public function __construct(
        private readonly array $agents,
    ) {}

    public function __invoke($data): mixed
    {
        assert($data instanceof AgentPacket);

        $agent = $this->agents[$data->agentId->value] ?? null;
        if (!$agent instanceof AbstractAgent) {
            throw new InvalidArgumentException(sprintf('Unknown agent "%s".', $data->agentId->value));
        }

        return $agent($data);
    }

    public static function key(AgentId $id): string
    {
        return $id->value;
    }
}
