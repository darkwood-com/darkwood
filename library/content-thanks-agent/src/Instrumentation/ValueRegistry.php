<?php

declare(strict_types=1);

namespace App\Instrumentation;

use App\Model\AgentId;

/**
 * Declares production value weights per agent.
 * ThanksAgent is deliberately +0.
 */
final class ValueRegistry
{
    public function valueFor(AgentId $agentId): int
    {
        return $agentId->valueWeight();
    }
}
