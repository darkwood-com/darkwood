<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class ContentAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'content',
            name: 'Content Agent',
            role: 'Content creation agent',
            description: 'Creates landing page and article draft content.',
            responsibilities: [
                'Draft landing page copy.',
                'Generate article draft variants.',
                'Keep tone aligned to brief.',
            ],
            capabilities: [
                new AgentCapability('copywriting'),
                new AgentCapability('editing'),
            ],
        );
    }
}
