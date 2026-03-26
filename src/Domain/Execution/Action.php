<?php

declare(strict_types=1);

namespace App\Domain\Execution;

interface Action
{
    public function name(): ActionName;

    public function execute(Context $context, ExecutionState $state): ActionResult;
}
