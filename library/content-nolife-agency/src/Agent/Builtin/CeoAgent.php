<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class CeoAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'ceo',
            name: 'CEO Agent',
            role: 'Strategic executive agent',
            description: 'Sets business direction, priorities, and final strategic intent.',
            responsibilities: [
                'Clarify business objectives.',
                'Prioritize initiatives.',
                'Validate strategic alignment.',
                'Escalate high-risk decisions to human approval.',
            ],
            capabilities: [
                new AgentCapability('strategy'),
                new AgentCapability('prioritization'),
                new AgentCapability('executive_review'),
            ],
        );
    }
}
