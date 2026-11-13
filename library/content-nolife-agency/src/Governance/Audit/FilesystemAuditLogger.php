<?php

namespace App\Governance\Audit;

final readonly class FilesystemAuditLogger implements AuditLoggerInterface
{
    public function __construct(
        private string $auditBaseDir,
    ) {
    }

    public function log(AuditEvent $event): void
    {
        $dir = rtrim($this->auditBaseDir, '/').'/agent-runs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = sprintf('%s/%s.jsonl', $dir, $event->runId);
        file_put_contents($path, json_encode($event->toArray(), \JSON_THROW_ON_ERROR)."\n", \FILE_APPEND);
    }
}
