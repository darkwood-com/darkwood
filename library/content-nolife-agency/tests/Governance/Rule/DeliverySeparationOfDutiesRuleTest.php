<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\DeliverySeparationOfDutiesRule;
use PHPUnit\Framework\TestCase;

final class DeliverySeparationOfDutiesRuleTest extends TestCase
{
    public function testDeveloperCannotApproveOwnOutput(): void
    {
        $rule = new DeliverySeparationOfDutiesRule();
        $violations = $rule->check(
            new AuditContext('r', 'developer', 'u'),
            new AgentInput([], ['approver_agent' => 'developer']),
        );
        self::assertNotEmpty($violations);
    }
}
