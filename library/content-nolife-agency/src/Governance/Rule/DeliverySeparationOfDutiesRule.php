<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class DeliverySeparationOfDutiesRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'delivery_separation_of_duties';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        $approver = $input->metadata['approver_agent'] ?? null;

        if ($context->agentKey === 'developer' && $approver === 'developer') {
            return [new PolicyViolation($this->name(), 'Developer cannot approve its own output.')];
        }

        return [];
    }
}
