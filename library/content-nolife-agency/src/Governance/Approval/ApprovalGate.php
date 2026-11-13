<?php

namespace App\Governance\Approval;

final class ApprovalGate
{
    /**
     * @param list<string> $reasons
     */
    public function evaluate(bool $approved, array $reasons = []): ApprovalDecision
    {
        return new ApprovalDecision(
            $approved ? ApprovalStatus::Approved : ApprovalStatus::Rejected,
            $reasons
        );
    }
}
