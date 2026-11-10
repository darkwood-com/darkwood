<?php

namespace App\Workflow;

final readonly class WorkflowDefinition
{
    /**
     * @param list<WorkflowState> $states
     * @param list<WorkflowStep> $steps
     */
    public function __construct(
        public string $key,
        public array $states,
        public array $steps,
    ) {
    }
}
