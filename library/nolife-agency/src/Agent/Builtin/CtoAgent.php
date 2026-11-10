<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class CtoAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'cto',
            name: 'CTO Agent',
            role: 'Technical governance and platform strategy agent',
            description: 'Owns technical standards, platform choices, and architectural risk.',
            responsibilities: [
                'Validate technical feasibility.',
                'Review architecture risks.',
                'Enforce engineering standards.',
                'Approve platform-level technical decisions.',
            ],
            capabilities: [
                new AgentCapability('technical_governance'),
                new AgentCapability('architecture_review'),
                new AgentCapability('risk_assessment'),
            ],
        );
    }
}
