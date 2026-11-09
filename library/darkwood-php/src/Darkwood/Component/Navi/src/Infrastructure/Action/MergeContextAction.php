<?php

declare(strict_types=1);

namespace Navi\Infrastructure\Action;

use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\Event;
use Navi\Domain\Execution\ExecutionState;

final class MergeContextAction implements Action
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private ActionName $name,
        private array $values
    ) {}

    public function name(): ActionName
    {
        return $this->name;
    }

    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        $updatedContext = $context->merge($this->values);

        return ActionResult::continueWith(
            $updatedContext,
            Event::fromName($this->name->toString(), $this->values)
        );
    }
}
