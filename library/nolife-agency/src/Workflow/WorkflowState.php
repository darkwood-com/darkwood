<?php

namespace App\Workflow;

final readonly class WorkflowState
{
    public function __construct(
        public string $name,
    ) {
    }
}
