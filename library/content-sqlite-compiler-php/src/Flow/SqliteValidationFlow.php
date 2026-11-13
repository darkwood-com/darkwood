<?php

declare(strict_types=1);

namespace App\Flow;

use App\Flow\Model\SqliteValidationState;
use App\Flow\Step\AssembleHarness;
use App\Flow\Step\AssembleSqlite;
use App\Flow\Step\CompileHarness;
use App\Flow\Step\CompileSqlite;
use App\Flow\Step\LinkSmokeExecutable;
use App\Flow\Step\RunSmokeTest;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\JobInterface;

final class SqliteValidationFlow
{
    public function __construct(
        private readonly CompileSqlite $compileSqlite,
        private readonly AssembleSqlite $assembleSqlite,
        private readonly CompileHarness $compileHarness,
        private readonly AssembleHarness $assembleHarness,
        private readonly LinkSmokeExecutable $linkSmokeExecutable,
        private readonly RunSmokeTest $runSmokeTest,
    ) {
    }

    public function run(SqliteValidationState $state): SqliteValidationState
    {
        $flow = (new FlowFactory())->create(function () {
            yield $this->timed('compile_sqlite', $this->compileSqlite);
            yield $this->timed('assemble_sqlite', $this->assembleSqlite);
            yield $this->timed('compile_harness', $this->compileHarness);
            yield $this->timed('assemble_harness', $this->assembleHarness);
            yield $this->timed('link', $this->linkSmokeExecutable);
            yield $this->timed('run', $this->runSmokeTest);
        });

        $flow(new Ip($state));
        $flow->await();

        if ($state->failure !== null) {
            throw new \RuntimeException($state->failure);
        }

        return $state;
    }

    /**
     * @param JobInterface<SqliteValidationState, SqliteValidationState> $job
     *
     * @return \Closure(SqliteValidationState): SqliteValidationState
     */
    private function timed(string $name, JobInterface $job): \Closure
    {
        return function (SqliteValidationState $state) use ($name, $job): SqliteValidationState {
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
