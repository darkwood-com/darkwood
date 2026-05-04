<?php

declare(strict_types=1);

namespace Navi\Application\Workflow;

use Navi\Domain\Execution\ExecutionState;

final readonly class WorkflowResult
{
    public function __construct(private ExecutionState $state) {}

    public function state(): ExecutionState
    {
        return $this->state;
    }
}
