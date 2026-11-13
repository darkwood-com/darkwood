<?php

namespace App\Governance;

final class PolicyChecker
{
    /**
     * @return array{allowed: bool, reasons: list<string>}
     */
    public function checkApproval(string $producerAgent, string $approverAgent): array
    {
        $reasons = [];

        if ($producerAgent === 'developer' && $approverAgent === 'developer') {
            $reasons[] = 'Developer agent cannot approve its own delivery.';
        }

        if ($producerAgent === 'content' && $approverAgent !== 'delivery_manager') {
            $reasons[] = 'Content outputs require Delivery Manager approval.';
        }

        return [
            'allowed' => $reasons === [],
            'reasons' => $reasons,
        ];
    }
}
