<?php

namespace App\Workflow;

final readonly class WorkflowStep
{
    public function __construct(
        public string $key,
        public string $agentKey,
        public string $fromState,
        public string $toState,
    ) {
    }
}
