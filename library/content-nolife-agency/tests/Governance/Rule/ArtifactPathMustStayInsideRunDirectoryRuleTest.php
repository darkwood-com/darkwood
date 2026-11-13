<?php

namespace App\Tests\Governance\Rule;

use App\Agent\Runtime\AgentInput;
use App\Agent\Runtime\AgentOutput;
use App\Governance\Audit\AuditContext;
use App\Governance\Rule\ArtifactPathMustStayInsideRunDirectoryRule;
use PHPUnit\Framework\TestCase;

final class ArtifactPathMustStayInsideRunDirectoryRuleTest extends TestCase
{
    public function testRejectsArtifactOutsideRunDirectory(): void
    {
        $rule = new ArtifactPathMustStayInsideRunDirectoryRule();
        $violations = $rule->check(
            new AuditContext('run', 'developer', 'tester'),
            new AgentInput([], ['run_dir' => '/tmp/run-1']),
            new AgentOutput([], ['/tmp/other/file.md']),
        );
        self::assertNotEmpty($violations);
    }
}
