<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow;

use App\TokenFlow\PipelineContext;
use App\TokenFlow\Step\PipelineStepInterface;
use Flow\DriverInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\Job\ClosureJob;

final class TokenPipelineFlowRunner implements PipelineRunnerInterface
{
    public function __construct(
        private readonly DriverInterface $driver,
    ) {}

    public function run(array $steps, PipelineContext $context): PipelineContext
    {
        return $this->runStepsThroughFlow($steps, $context);
    }

    /**
     * @param list<PipelineStepInterface> $steps
     */
    private function runStepsThroughFlow(array $steps, PipelineContext $context): PipelineContext
    {
        if ($steps === []) {
            return $context;
        }

        $config = ['driver' => $this->driver];
        $remaining = $steps;
        $first = array_shift($remaining);

        $flow = (new FlowFactory())->createFlow(
            new ClosureJob(fn (PipelineContext $ctx): PipelineContext => $first->apply($ctx)),
            $config,
        );

        foreach ($remaining as $step) {
            $flow = $flow |> (fn ($flow) => $flow->fn(
                new ClosureJob(fn (PipelineContext $ctx): PipelineContext => $step->apply($ctx)),
            ));
        }

        $flow(new Ip($context));
        $flow->await();

        return $context;
    }
}
