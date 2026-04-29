<?php

namespace App\Governance\Rule;

use App\Governance\Audit\AuditContext;
use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Policy\PolicyViolation;

interface GovernanceRuleInterface
{
    public function name(): string;

    /**
     * @return list<PolicyViolation>
     */
    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array;
}
