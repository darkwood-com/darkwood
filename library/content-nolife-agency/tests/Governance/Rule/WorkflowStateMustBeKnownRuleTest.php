<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\WorkflowStateMustBeKnownRule;
use PHPUnit\Framework\TestCase;

final class WorkflowStateMustBeKnownRuleTest extends TestCase
{
    public function testRejectsUnknownWorkflowState(): void
    {
        $rule = new WorkflowStateMustBeKnownRule();
        $violations = $rule->check(
            new AuditContext('run', 'ceo', 'tester'),
            new AgentInput([], ['workflow_state' => 'unknown', 'known_workflow_states' => ['a', 'b']]),
        );
        self::assertNotEmpty($violations);
    }
}
