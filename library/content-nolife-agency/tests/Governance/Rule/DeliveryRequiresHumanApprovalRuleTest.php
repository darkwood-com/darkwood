<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\DeliveryRequiresHumanApprovalRule;
use PHPUnit\Framework\TestCase;

final class DeliveryRequiresHumanApprovalRuleTest extends TestCase
{
    public function testRequiresApprovalForFinalState(): void
    {
        $rule = new DeliveryRequiresHumanApprovalRule();
        $violations = $rule->check(
            new AuditContext('run', 'delivery_manager', 'tester'),
            new AgentInput([], ['workflow_state' => 'human_approval_required', 'approved_by_human' => false]),
        );
        self::assertNotEmpty($violations);
    }
}
