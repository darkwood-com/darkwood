<?php

namespace App\Governance\Policy;

final readonly class PolicyDecision
{
    /**
     * @param list<PolicyViolation> $violations
     */
    public function __construct(
        public PolicyDecisionType $type,
        public array $violations = [],
    ) {
    }

    public function isRejected(): bool
    {
        return $this->type === PolicyDecisionType::Reject;
    }

    public function requiresApproval(): bool
    {
        return $this->type === PolicyDecisionType::RequireApproval;
    }
}
