<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class ProductOwnerAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'product_owner',
            name: 'Product Owner Agent',
            role: 'Product scope and acceptance criteria agent',
            description: 'Turns business intent into scoped, testable product requirements.',
            responsibilities: [
                'Define user stories.',
                'Write acceptance criteria.',
                'Manage scope boundaries.',
                'Confirm feature readiness.',
            ],
            capabilities: [
                new AgentCapability('requirements'),
                new AgentCapability('acceptance_criteria'),
                new AgentCapability('scope_control'),
            ],
        );
    }
}
