<?php

namespace App\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\PolicyViolation;

final class NoDirectCrossRepoCliCouplingRule implements GovernanceRuleInterface
{
    public function name(): string
    {
        return 'no_direct_cross_repo_cli_coupling';
    }

    public function check(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): array
    {
        $usesCli = (bool) ($input->metadata['uses_cross_repo_cli'] ?? false);
        if ($usesCli) {
            return [new PolicyViolation($this->name(), 'Cross-repo integration must use typed clients, not CLI coupling.')];
        }

        return [];
    }
}
