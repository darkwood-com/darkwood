<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Builtin\CeoAgent;
use App\Agent\Registry\ConfiguredAgentRegistry;
use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\AgentMustExistRule;
use PHPUnit\Framework\TestCase;

final class AgentMustExistRuleTest extends TestCase
{
    public function testRejectsUnknownAgentKey(): void
    {
        $rule = new AgentMustExistRule(new ConfiguredAgentRegistry([new CeoAgent()]));
        $violations = $rule->check(new AuditContext('r', 'unknown', 'u'), new AgentInput([]));
        self::assertNotEmpty($violations);
    }
}
