<?php

namespace App\Governance;

final readonly class AuditLogger
{
    public function __construct(
        private string $auditDir,
    ) {
    }

    public function log(AuditRecord $record): string
    {
        if (!is_dir($this->auditDir)) {
            mkdir($this->auditDir, 0775, true);
        }

        $path = rtrim($this->auditDir, '/').'/'.$record->runId.'.jsonl';
        $line = json_encode($record->toArray(), \JSON_THROW_ON_ERROR)."\n";
        file_put_contents($path, $line, \FILE_APPEND);

        return $path;
    }
}
