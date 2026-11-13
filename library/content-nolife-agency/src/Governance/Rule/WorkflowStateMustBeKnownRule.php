<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class WorkflowStateMustBeKnownRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'workflow_state_must_be_known';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        $knownStates = $input->metadata['known_workflow_states'] ?? [];
        $currentState = $input->metadata['workflow_state'] ?? null;

        if (!is_string($currentState) || $currentState === '') {
            return [];
        }

        if (is_array($knownStates) && in_array($currentState, $knownStates, true)) {
            return [];
        }

        return [new PolicyViolation($this->name(), 'Workflow state must be one of known workflow states.')];
    }
}
