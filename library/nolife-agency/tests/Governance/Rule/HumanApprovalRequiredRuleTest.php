<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\HumanApprovalRequiredRule;
use PHPUnit\Framework\TestCase;

final class HumanApprovalRequiredRuleTest extends TestCase
{
    public function testHighRiskActionRequiresApproval(): void
    {
        $rule = new HumanApprovalRequiredRule();
        $violations = $rule->check(
            new AuditContext('r', 'ceo', 'u'),
            new AgentInput([], ['action' => 'deploy', 'approved_by_human' => false]),
        );
        self::assertNotEmpty($violations);
    }
}
