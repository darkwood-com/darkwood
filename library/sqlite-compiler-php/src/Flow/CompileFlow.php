<?php

declare(strict_types=1);

namespace App\Flow;

use App\Flow\Model\CompileState;
use App\Flow\Step\AssembleObject;
use App\Flow\Step\CompileSource;
use App\Flow\Step\LinkExecutable;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\JobInterface;

final class CompileFlow
{
    public function __construct(
        private readonly CompileSource $compileSource,
        private readonly AssembleObject $assembleObject,
        private readonly LinkExecutable $linkExecutable,
    ) {
    }

    public function run(CompileState $state, bool $link = false): CompileState
    {
        $flow = (new FlowFactory())->create(function () use ($link) {
            yield $this->timed('compile', $this->compileSource);
            if ($link) {
                yield $this->timed('assemble', $this->assembleObject);
                yield $this->timed('link', $this->linkExecutable);
            }
        });

        $flow(new Ip($state));
        $flow->await();

        if ($state->failure !== null) {
            throw new \RuntimeException($state->failure);
        }

        return $state;
    }

    /**
     * @param JobInterface<CompileState, CompileState> $job
     *
     * @return \Closure(CompileState): CompileState
     */
    private function timed(string $name, JobInterface $job): \Closure
    {
        return function (CompileState $state) use ($name, $job): CompileState {
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
