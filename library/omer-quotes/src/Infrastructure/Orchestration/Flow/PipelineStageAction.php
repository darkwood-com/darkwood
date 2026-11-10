<?php
declare(strict_types=1);
namespace App\Infrastructure\Orchestration\Flow;
use Navi\Domain\Execution\Action;
use Navi\Domain\Execution\ActionName;
use Navi\Domain\Execution\ActionResult;
use Navi\Domain\Execution\Context;
use Navi\Domain\Execution\ExecutionState;

final readonly class PipelineStageAction implements Action
{
    public function __construct(private string $actionName, private \Closure $handler) {}
    public function name(): ActionName { return ActionName::fromString($this->actionName); }
    public function execute(Context $context, ExecutionState $state): ActionResult
    {
        return ActionResult::continueWith(($this->handler)($context));
    }
}
