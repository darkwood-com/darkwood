<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class DeliveryRequiresHumanApprovalRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'delivery_requires_human_approval';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        $state = $input->metadata['workflow_state'] ?? null;
        $approved = (bool) ($input->metadata['approved_by_human'] ?? false);

        if ($state === 'human_approval_required' && !$approved) {
            return [new PolicyViolation($this->name(), 'Human approval is required for final delivery state.')];
        }

        return [];
    }
}
