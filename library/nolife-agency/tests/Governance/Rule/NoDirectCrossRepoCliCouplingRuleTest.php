<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\NoDirectCrossRepoCliCouplingRule;
use PHPUnit\Framework\TestCase;

final class NoDirectCrossRepoCliCouplingRuleTest extends TestCase
{
    public function testRejectsDirectCliCoupling(): void
    {
        $rule = new NoDirectCrossRepoCliCouplingRule();
        $violations = $rule->check(
            new AuditContext('r', 'architect', 'u'),
            new AgentInput([], ['uses_cross_repo_cli' => true]),
        );
        self::assertNotEmpty($violations);
    }
}
