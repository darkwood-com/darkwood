<?php

declare(strict_types=1);

namespace App\Experiment;

use App\Flow\EmbeddedReactDriver;
use App\Flow\EmbeddedReactFlowRunner;
use Flow\Driver\ReactDriver;
use Flow\DriverInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Flow\IpStrategyInterface;
use React\Promise\PromiseInterface;

/**
 * Compares sequential vs concurrent Ip processing using native darkwood/flow
 * MaxIpStrategy.
 */
final class AsyncExecutionExperiment
{
    /**
     * CLI entry: owns the ReactPHP loop via ReactDriver::await().
     *
     * @return array<string, mixed>
     */
    public function runBlocking(float $delaySeconds = 0.05, int $items = 3): array
    {
        // MaxIpStrategy(1) forces one in-flight Ip (true sequential under async drivers).
        // LinearIpStrategy alone still overlaps jobs because the driver pulls the next Ip
        // while prior async work is outstanding.
        $driver = new ReactDriver();
        $sequential = $this->measureBlocking($driver, 'sequential', new MaxIpStrategy(1), $delaySeconds, $items);
        $concurrent = $this->measureBlocking($driver, 'concurrent', new MaxIpStrategy($items), $delaySeconds, $items);

        return $this->summarize($sequential, $concurrent, $delaySeconds, $items, 'ReactDriver::await (owns loop)');
    }

    /**
     * HTTP-safe entry: EmbeddedReactDriver, does not call Loop::stop().
     *
     * @return PromiseInterface<array<string, mixed>>
     */
    public function runAsync(EmbeddedReactDriver $driver, EmbeddedReactFlowRunner $runner, float $delaySeconds = 0.05, int $items = 3): PromiseInterface
    {
        return $this->measureAsync($driver, $runner, 'sequential', new MaxIpStrategy(1), $delaySeconds, $items)
            ->then(function (array $sequential) use ($driver, $runner, $delaySeconds, $items) {
                return $this->measureAsync($driver, $runner, 'concurrent', new MaxIpStrategy($items), $delaySeconds, $items)
                    ->then(fn (array $concurrent) => $this->summarize(
                        $sequential,
                        $concurrent,
                        $delaySeconds,
                        $items,
                        'EmbeddedReactDriver (shared Framework X loop)'
                    ));
            });
    }

    /**
     * @return array<string, mixed>
     */
    private function summarize(array $sequential, array $concurrent, float $delaySeconds, int $items, string $driverMode): array
    {
        return [
            'description' => 'Native Flow Ip concurrency via MaxIpStrategy(1) vs MaxIpStrategy(N). Each Ip runs one delayed job.',
            'driverMode' => $driverMode,
            'delaySecondsPerIp' => $delaySeconds,
            'ipCount' => $items,
            'sequential' => $sequential,
            'concurrent' => $concurrent,
            'speedup' => $sequential['elapsedMs'] > 0
                ? round($sequential['elapsedMs'] / max(0.001, $concurrent['elapsedMs']), 2)
                : null,
            'notes' => [
                'Timings are local and non-scientific.',
                'Concurrency comes from darkwood/flow MaxIpStrategy + React-compatible driver delays.',
                'This is Ip-level concurrency, not a parallel DAG of steps within one Ip.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function measureBlocking(
        DriverInterface $driver,
        string $label,
        IpStrategyInterface $strategy,
        float $delaySeconds,
        int $items,
    ): array {
        $started = hrtime(true);
        $completed = [];

        $job = static function (object $data) use ($driver, $delaySeconds, &$completed): object {
            $driver->delay($delaySeconds);
            $completed[] = $data->id;

            return $data;
        };

        $flow = (new FlowFactory($driver))->create(static function () use ($job, $strategy) {
            yield [$job, null, $strategy];
        }, ['driver' => $driver]);

        for ($i = 1; $i <= $items; $i++) {
            $flow(new Ip((object) ['id' => $i, 'label' => $label]));
        }
        $flow->await();

        return [
            'label' => $label,
            'strategy' => $strategy::class,
            'completedIds' => $completed,
            'elapsedMs' => round((hrtime(true) - $started) / 1e6, 3),
        ];
    }

    /**
     * @return PromiseInterface<array<string, mixed>>
     */
    private function measureAsync(
        EmbeddedReactDriver $driver,
        EmbeddedReactFlowRunner $runner,
        string $label,
        IpStrategyInterface $strategy,
        float $delaySeconds,
        int $items,
    ): PromiseInterface {
        $started = hrtime(true);
        $completed = [];
        $collector = (object) ['done' => 0, 'total' => $items];

        $job = static function (object $data) use ($driver, $delaySeconds, &$completed, $collector): object {
            $driver->delay($delaySeconds);
            $completed[] = $data->id;
            $collector->done++;

            return $data;
        };

        $flow = (new FlowFactory($driver))->create(static function () use ($job, $strategy) {
            yield [$job, null, $strategy];
        }, ['driver' => $driver]);

        $done = $driver->whenComplete();
        for ($i = 1; $i <= $items; $i++) {
            $flow(new Ip((object) ['id' => $i, 'label' => $label]));
        }
        $flow->await();

        return $done->then(function () use ($label, $strategy, &$completed, $started) {
            return [
                'label' => $label,
                'strategy' => $strategy::class,
                'completedIds' => $completed,
                'elapsedMs' => round((hrtime(true) - $started) / 1e6, 3),
            ];
        });
    }
}
