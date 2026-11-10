<?php

namespace App\Governance\Approval;

final readonly class ApprovalDecision
{
    /**
     * @param list<string> $reasons
     */
    public function __construct(
        public ApprovalStatus $status,
        public array $reasons = [],
    ) {
    }
}
