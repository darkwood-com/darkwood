<?php

namespace App\Agent\Builtin;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;

final class QaAgent extends AbstractBuiltinAgent
{
    public function definition(): AgentDefinition
    {
        return new AgentDefinition(
            key: 'qa',
            name: 'QA Agent',
            role: 'Quality assurance agent',
            description: 'Verifies quality gates and release readiness evidence.',
            responsibilities: [
                'Run quality checklist.',
                'Validate acceptance criteria.',
                'Raise final approval requirement.',
            ],
            capabilities: [
                new AgentCapability('qa'),
                new AgentCapability('verification'),
            ],
        );
    }
}
