<?php

namespace App\Governance;

final readonly class AuditRecord
{
    /**
     * @param array<string, mixed> $toolCalls
     * @param array<string> $filesRead
     * @param array<string> $filesWritten
     * @param array<string, mixed> $policyDecisions
     * @param array<string, mixed> $finalArtifacts
     */
    public function __construct(
        public string $runId,
        public string $agentName,
        public string $agentRole,
        public string $inputHash,
        public string $outputHash,
        public string $requester,
        public string $timestampStart,
        public string $timestampEnd,
        public array $toolCalls,
        public array $filesRead,
        public array $filesWritten,
        public string $approvalStatus,
        public ?string $traceId,
        public array $policyDecisions,
        public array $finalArtifacts,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'run_id' => $this->runId,
            'agent_name' => $this->agentName,
            'agent_role' => $this->agentRole,
            'input_hash' => $this->inputHash,
            'output_hash' => $this->outputHash,
            'user' => $this->requester,
            'timestamp_start' => $this->timestampStart,
            'timestamp_end' => $this->timestampEnd,
            'tool_calls' => $this->toolCalls,
            'files_read' => $this->filesRead,
            'files_written' => $this->filesWritten,
            'approval_status' => $this->approvalStatus,
            'trace_id' => $this->traceId,
            'policy_decisions' => $this->policyDecisions,
            'final_artifacts' => $this->finalArtifacts,
        ];
    }
}
