<?php

declare(strict_types=1);

namespace App\Domain\Execution;

final readonly class ExecutionState
{
    /**
     * @param ActionName[] $completedActions
     * @param Event[] $events
     */
    private function __construct(
        private ExecutionId $executionId,
        private Context $context,
        private array $completedActions,
        private array $events
    ) {
    }

    public static function start(Context $context): self
    {
        return new self(ExecutionId::generate(), $context, [], []);
    }

    public function executionId(): ExecutionId
    {
        return $this->executionId;
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
            $this->executionId,
            $result->context(),
            [...$this->completedActions, $actionName],
            [...$this->events, ...$result->events()]
        );
    }
}
