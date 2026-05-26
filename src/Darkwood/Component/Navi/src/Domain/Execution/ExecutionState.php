<?php

declare(strict_types=1);

namespace Navi\Domain\Execution;

final class ExecutionState
{
    /**
     * @param ActionName[] $completedActions
     * @param Event[]      $events
     */
    private function __construct(
        private Context $context,
        private array $completedActions,
        private array $events
    ) {}

    public static function start(Context $context): self
    {
        return new self($context, [], []);
    }

    public function context(): Context
    {
        return $this->context;
    }

    /**
     * @return ActionName[]
     */
    public function completedActions(): array
    {
        return $this->completedActions;
    }

    /**
     * @return Event[]
     */
    public function events(): array
    {
        return $this->events;
    }

    public function record(ActionName $actionName, ActionResult $result): self
    {
        return new self(
            $result->context(),
            [...$this->completedActions, $actionName],
            [...$this->events, ...$result->events()]
        );
    }
}
