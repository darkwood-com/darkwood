<?php

declare(strict_types=1);

namespace Navi\Domain\Execution;

interface Action
{
    public function name(): ActionName;

    public function execute(Context $context, ExecutionState $state): ActionResult;
}
