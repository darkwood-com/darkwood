<?php

declare(strict_types=1);

namespace Navi\Application\Workflow;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\ExecutionState;

final class WorkflowRunner
{
    /**
     * @param iterable<Action> $actions
     */
    public function run(Context $context, iterable $actions): WorkflowResult
    {
        $state = ExecutionState::start($context);

        foreach ($actions as $action) {
            $result = $action->execute($state->context(), $state);
            $state = $state->record($action->name(), $result);
        }

        return new WorkflowResult($state);
    }
}
