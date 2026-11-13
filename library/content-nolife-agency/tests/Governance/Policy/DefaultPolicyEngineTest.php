<?php

namespace App\Tests\Governance\Policy;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Policy\DefaultPolicyEngine;
use App\Governance\Policy\PolicyDecisionType;
use App\Governance\Rule\HumanApprovalRequiredRule;
use PHPUnit\Framework\TestCase;

final class DefaultPolicyEngineTest extends TestCase
{
    public function testReturnsRequireApprovalForHighRiskAction(): void
    {
        $engine = new DefaultPolicyEngine([new HumanApprovalRequiredRule()]);
        $decision = $engine->evaluate(
            new AuditContext('r1', 'ceo', 'tester'),
            new AgentInput([], ['action' => 'deploy', 'approved_by_human' => false]),
        );

        self::assertSame(PolicyDecisionType::RequireApproval, $decision->type);
    }
}
