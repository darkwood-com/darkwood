<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class HumanApprovalRequiredRule implements GovernanceRuleInterface
{
    private const HIGH_RISK_ACTIONS = [
        'publish_client_delivery',
        'deploy',
        'modify_production_data',
        'send_email_to_client',
        'generate_invoice',
        'delete_artifact',
        'execute_cross_repo_write',
        'change_governance_policy',
    ];

    public function name(): string
    {
        return 'human_approval_required';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        $action = $input->metadata['action'] ?? null;
        $approved = (bool) ($input->metadata['approved_by_human'] ?? false);

        if (is_string($action) && in_array($action, self::HIGH_RISK_ACTIONS, true) && !$approved) {
            return [new PolicyViolation($this->name(), 'High-risk action requires human approval.')];
        }

        return [];
    }
}
