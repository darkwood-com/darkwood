<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class AgentInputMustBeAuditableRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'agent_input_must_be_auditable';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        try {
            json_encode($input->payload, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [new PolicyViolation($this->name(), 'Input payload is not serializable.')];
        }

        return [];
    }
}
