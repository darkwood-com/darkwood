<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow;

use App\TokenFlow\PipelineContext;
use App\TokenFlow\Step\PipelineStepInterface;

interface PipelineRunnerInterface
{
    /**
     * @param list<PipelineStepInterface> $steps
     */
    public function run(array $steps, PipelineContext $context): PipelineContext;
}
