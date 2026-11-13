<?php

namespace App\Integration\Flow;

final readonly class WorkflowResult
{
    /**
     * @param list<string> $completedSteps
     * @param list<string> $outputArtifactPaths
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $workflowKey,
        public string $runId,
        public string $finalState,
        public array $completedSteps = [],
        public array $outputArtifactPaths = [],
        public string $status,
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'workflow_key' => $this->workflowKey,
            'run_id' => $this->runId,
            'final_state' => $this->finalState,
            'completed_steps' => $this->completedSteps,
            'output_artifact_paths' => $this->outputArtifactPaths,
            'status' => $this->status,
            'metadata' => $this->metadata,
        ];
    }
}
