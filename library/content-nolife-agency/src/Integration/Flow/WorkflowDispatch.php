<?php

namespace App\Integration\Flow;

final readonly class WorkflowDispatch
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $workflowKey,
        public string $runId,
        public array $payload,
        public array $context = [],
        public array $workflowSteps = [],
    ) {
    }
}
