<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class DeveloperAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'developer',
            name: 'Developer Agent',
            role: 'Implementation agent',
            description: 'Implements approved technical tasks in Symfony and related packages.',
            responsibilities: [
                'Implement scoped code changes.',
                'Add tests.',
                'Respect architecture and governance decisions.',
                'Report modified files and verification results.',
            ],
            capabilities: [
                new AgentCapability('php'),
                new AgentCapability('symfony'),
                new AgentCapability('testing'),
            ],
        );
    }
}
