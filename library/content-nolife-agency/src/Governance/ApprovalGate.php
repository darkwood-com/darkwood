<?php

namespace App\Governance;

final readonly class ApprovalGate
{
    public function __construct(
        private PolicyChecker $policyChecker,
    ) {
    }

    /**
     * @return array{approved: bool, reasons: list<string>}
     */
    public function review(string $producerAgent, string $approverAgent): array
    {
        $result = $this->policyChecker->checkApproval($producerAgent, $approverAgent);

        return [
            'approved' => $result['allowed'],
            'reasons' => $result['reasons'],
        ];
    }
}
