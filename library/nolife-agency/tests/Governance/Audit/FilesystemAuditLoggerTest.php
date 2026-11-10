<?php

namespace App\Tests\Governance\Audit;

use App\Governance\Audit\AuditEvent;
use App\Governance\Audit\AuditEventType;
use App\Governance\Audit\FilesystemAuditLogger;
use PHPUnit\Framework\TestCase;

final class FilesystemAuditLoggerTest extends TestCase
{
    public function testWritesJsonlForRun(): void
    {
        $tmpDir = sys_get_temp_dir().'/nolife-audit-'.uniqid('', true);
        $logger = new FilesystemAuditLogger($tmpDir);
        $logger->log(new AuditEvent(
            eventId: 'e1',
            eventType: AuditEventType::AgentRunRequested,
            runId: 'r1',
            traceId: null,
            agentKey: 'ceo',
            requester: 'tester',
            projectKey: null,
            timestamp: '2026-01-01T00:00:00+00:00',
            inputHash: null,
            outputHash: null,
            decision: null,
            message: 'x',
            metadata: [],
        ));

        self::assertFileExists($tmpDir.'/agent-runs/r1.jsonl');
    }
}
