<?php

namespace App\Governance\Audit;

final readonly class AuditEvent
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $eventId,
        public AuditEventType $eventType,
        public string $runId,
        public ?string $traceId,
        public string $agentKey,
        public string $requester,
        public ?string $projectKey,
        public string $timestamp,
        public ?string $inputHash,
        public ?string $outputHash,
        public ?string $decision,
        public string $message,
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'event_id' => $this->eventId,
            'event_type' => $this->eventType->value,
            'run_id' => $this->runId,
            'trace_id' => $this->traceId,
            'agent_key' => $this->agentKey,
            'requester' => $this->requester,
            'project_key' => $this->projectKey,
            'timestamp' => $this->timestamp,
            'input_hash' => $this->inputHash,
            'output_hash' => $this->outputHash,
            'decision' => $this->decision,
            'message' => $this->message,
            'metadata' => $this->metadata,
        ];
    }
}
