<?php

declare(strict_types=1);

namespace App\Flow;

use App\Flow\Model\ProbeState;
use App\Flow\Step\RunProbe;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\JobInterface;

final class ProbeRunFlow
{
    public function __construct(
        private readonly RunProbe $runProbe,
    ) {
    }

    public function run(ProbeState $state): ProbeState
    {
        $flow = (new FlowFactory())->create(function () {
            yield $this->timed('probe', $this->runProbe);
        });

        $flow(new Ip($state));
        $flow->await();

        if ($state->failure !== null) {
            throw new \RuntimeException($state->failure);
        }

        return $state;
    }

    /**
     * @param JobInterface<ProbeState, ProbeState> $job
     *
     * @return \Closure(ProbeState): ProbeState
     */
    private function timed(string $name, JobInterface $job): \Closure
    {
        return function (ProbeState $state) use ($name, $job): ProbeState {
            if ($state->failure !== null) {
                return $state;
            }

            $start = hrtime(true);
            try {
                $job($state);
            } catch (\Throwable $e) {
                $state->failure = $e->getMessage();
            }
            $state->timings[$name] = (hrtime(true) - $start) / 1_000_000;

            return $state;
        };
    }
}
