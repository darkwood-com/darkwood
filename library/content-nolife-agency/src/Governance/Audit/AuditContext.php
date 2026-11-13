<?php

namespace App\Governance\Audit;

final readonly class AuditContext
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $runId,
        public string $agentKey,
        public string $requester,
        public ?string $projectKey = null,
        public ?string $traceId = null,
        public array $metadata = [],
    ) {
    }
}
