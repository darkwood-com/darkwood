<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class ArchitectAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'architect',
            name: 'Architect Agent',
            role: 'System architecture agent',
            description: 'Designs service boundaries, workflows, contracts, and implementation plans.',
            responsibilities: [
                'Create technical plans.',
                'Define Symfony services and interfaces.',
                'Design Flow workflow boundaries.',
                'Identify Navi trace points.',
            ],
            capabilities: [
                new AgentCapability('system_design'),
                new AgentCapability('workflow_design'),
                new AgentCapability('integration_design'),
            ],
        );
    }
}
