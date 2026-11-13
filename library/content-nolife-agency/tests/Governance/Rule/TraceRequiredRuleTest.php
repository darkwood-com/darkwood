<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\TraceRequiredRule;
use PHPUnit\Framework\TestCase;

final class TraceRequiredRuleTest extends TestCase
{
    public function testRequiresTrace(): void
    {
        $rule = new TraceRequiredRule();
        $violations = $rule->check(new AuditContext('r', 'ceo', 'u', null, null), new AgentInput([]));
        self::assertNotEmpty($violations);
    }
}
