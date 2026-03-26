<?php

declare(strict_types=1);

namespace App\Infrastructure\Action;

use App\Domain\Execution\Action;
use App\Domain\Execution\ActionName;
use App\Domain\Execution\ActionResult;
use App\Domain\Execution\Context;
use App\Domain\Execution\Event;
use App\Domain\Execution\ExecutionState;

final readonly class MergeContextAction implements Action
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(
        private ActionName $name,
        private array $values
    ) {
    }

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
