<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

/**
 * Fan-in synthesis over results already gathered in RunStore.
 */
final class SummaryAgent extends AbstractAgent
{
    public function id(): AgentId
    {
        return AgentId::Summary;
    }

    protected function ioSeconds(): float
    {
        return 0.05;
    }

    protected function produce(AgentPacket $packet): array
    {
        $results = $this->store->all();
        $value = 0;
        $work = 0;
        $noise = 0;
        foreach ($results as $result) {
            if ($result->agentId === AgentId::Summary) {
                continue;
            }
            $value += $result->value;
            if ($result->didWork) {
                ++$work;
            } else {
                ++$noise;
            }
        }

        $output = sprintf(
            'Summary: %d agents stored, useful=%d noise=%d aggregateValue=%d',
            count($results),
            $work,
            $noise,
            $value,
        );

        return [
            'output' => $output,
            'tokens' => 48 + (count($results) * 8),
            'messages' => 1 + count($results),
            'didWork' => true,
            'payload' => [
                'aggregateValue' => $value,
                'usefulAgents' => $work,
                'noiseAgents' => $noise,
            ],
        ];
    }
}
