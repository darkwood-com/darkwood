<?php

declare(strict_types=1);

namespace App\Io;

/**
 * Baseline: one I/O task after another. Lowest WIP, highest wall time.
 */
final class SequentialRunner
{
    /**
     * @param list<array{id: string, delay: float, value: int, messages: int}> $tasks
     */
    public function run(array $tasks): BenchResult
    {
        $wallStart = hrtime(true);
        $busy = 0.0;
        $cost = 0.0;
        $value = 0;
        $messages = 0;
        $thanks = 0;

        foreach ($tasks as $task) {
            $t0 = hrtime(true);
            usleep((int) ($task['delay'] * 1e6));
            $busy += (hrtime(true) - $t0) / 1e6;
            $value += $task['value'];
            $messages += $task['messages'];
            $cost += 0.0005 + ($task['messages'] * 0.0003);
            if ('ThanksAgent' === $task['id']) {
                ++$thanks;
            }
        }

        $wall = (hrtime(true) - $wallStart) / 1e6;

        return new BenchResult(
            runner: 'sequential',
            wallTimeMs: $wall,
            busyTimeMs: $busy,
            idleTimeMs: max(0, $wall - $busy),
            tasksCompleted: count($tasks),
            thanksIncluded: $thanks,
            totalCost: $cost,
            totalValue: $value,
            totalMessages: $messages,
        );
    }
}
