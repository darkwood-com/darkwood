<?php

declare(strict_types=1);

namespace App\Application\Workflow;

use App\Domain\Execution\ExecutionState;

final readonly class WorkflowResult
{
    public function __construct(private ExecutionState $state)
    {
    }

    public function state(): ExecutionState
    {
        return $this->state;
    }
}
