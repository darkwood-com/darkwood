<?php

namespace App\Governance\Audit;

interface AuditLoggerInterface
{
    public function log(AuditEvent $event): void;
}
