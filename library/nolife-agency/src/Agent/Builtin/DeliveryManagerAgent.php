<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class DeliveryManagerAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'delivery_manager',
            name: 'Delivery Manager Agent',
            role: 'Delivery control and approval agent',
            description: 'Coordinates delivery quality, governance, and release readiness.',
            responsibilities: [
                'Review outputs from other agents.',
                'Check acceptance criteria.',
                'Verify audit and trace records.',
                'Approve or reject delivery packages.',
            ],
            capabilities: [
                new AgentCapability('delivery_review'),
                new AgentCapability('quality_gate'),
                new AgentCapability('approval_control'),
            ],
        );
    }
}
