<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class TraceRequiredRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'trace_required';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        if ($context->traceId === null || $context->traceId === '') {
            return [new PolicyViolation($this->name(), 'Trace context is required.')];
        }

        return [];
    }
}
