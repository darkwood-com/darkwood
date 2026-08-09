<?php

declare(strict_types=1);

namespace App\Agent;

use App\Model\AgentId;
use App\Model\AgentPacket;

/**
 * The ultimate anti-pattern: consumes scheduling/context/logging, produces no value.
 */
final class ThanksAgent extends AbstractAgent
{
    private const RESPONSES = ['Thanks.', 'Looks good.', 'Good job.'];

    public function id(): AgentId
    {
        return AgentId::Thanks;
    }

    protected function ioSeconds(): float
    {
        // Still occupies a WIP slot and goes through the scheduler.
        return 0.04;
    }

    protected function produce(AgentPacket $packet): array
    {
        $output = self::RESPONSES[array_rand(self::RESPONSES)];

        return [
            'output' => $output,
            'tokens' => 2,
            'messages' => 28, // noise: many empty acknowledgements
            'didWork' => false,
            'payload' => [
                'analyzed' => false,
                'transformed' => false,
                'expertise' => null,
                'contextHeld' => false,
            ],
        ];
    }
}
