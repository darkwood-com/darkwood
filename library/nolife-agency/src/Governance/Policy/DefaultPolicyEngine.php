<?php

namespace App\Governance\Policy;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\GovernanceRuleInterface;

final readonly class DefaultPolicyEngine implements PolicyEngineInterface
{
    /**
     * @param iterable<GovernanceRuleInterface> $rules
     */
    public function __construct(private iterable $rules)
    {
    }

    public function evaluate(AuditContext $context, AgentInput $input, ?AgentOutput $output = null): PolicyDecision
    {
        $violations = [];
        $requireApproval = false;

        foreach ($this->rules as $rule) {
            $ruleViolations = $rule->check($context, $input, $output);
            $violations = [...$violations, ...$ruleViolations];
            if ($rule->name() === 'human_approval_required' && $ruleViolations !== []) {
                $requireApproval = true;
            }
        }

        if ($violations === []) {
            return new PolicyDecision(PolicyDecisionType::Pass);
        }

        if ($requireApproval) {
            return new PolicyDecision(PolicyDecisionType::RequireApproval, $violations);
        }

        return new PolicyDecision(PolicyDecisionType::Reject, $violations);
    }
}
