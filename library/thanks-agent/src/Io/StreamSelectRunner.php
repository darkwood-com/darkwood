<?php

declare(strict_types=1);

namespace App\Io;

/**
 * Classic multiplexing via stream_select().
 */
final class StreamSelectRunner
{
    public function __construct(
        private readonly DelayedStreamFactory $streams = new DelayedStreamFactory(),
    ) {}

    /**
     * @param list<array{id: string, delay: float, value: int, messages: int}> $tasks
     */
    public function run(array $tasks): BenchResult
    {
        $wallStart = hrtime(true);
        $handles = [];
        $meta = [];
        $thanks = 0;
        $value = 0;
        $messages = 0;
        $cost = 0.0;

        foreach ($tasks as $i => $task) {
            $pair = $this->streams->openPair();
            $handles[$i] = $pair['read'];
            $meta[$i] = [
                'task' => $task,
                'write' => $pair['write'],
                'due' => microtime(true) + $task['delay'],
                'written' => false,
                'done' => false,
            ];
        }

        $completed = 0;
        $busy = 0.0;

        while ($completed < count($tasks)) {
            $now = microtime(true);
            foreach ($meta as $i => &$m) {
                if (!$m['written'] && $now >= $m['due']) {
                    fwrite($m['write'], '1');
                    fclose($m['write']);
                    $m['written'] = true;
                }
            }
            unset($m);

            $read = [];
            foreach ($handles as $i => $stream) {
                if (!$meta[$i]['done'] && is_resource($stream)) {
                    $read[] = $stream;
                }
            }

            if ([] === $read) {
                usleep(1000);
                continue;
            }

            $write = null;
            $except = null;
            $t0 = hrtime(true);
            $n = @stream_select($read, $write, $except, 0, 20000);
            $busy += (hrtime(true) - $t0) / 1e6;

            if (false === $n || 0 === $n) {
                continue;
            }

            foreach ($read as $ready) {
                foreach ($handles as $i => $stream) {
                    if ($stream !== $ready || $meta[$i]['done']) {
                        continue;
                    }
                    stream_get_contents($ready);
                    fclose($ready);
                    $meta[$i]['done'] = true;
                    ++$completed;
                    $task = $meta[$i]['task'];
                    $value += $task['value'];
                    $messages += $task['messages'];
                    $cost += 0.0005 + ($task['messages'] * 0.0003);
                    if ('ThanksAgent' === $task['id']) {
                        ++$thanks;
                    }
                }
            }
        }

        $wall = (hrtime(true) - $wallStart) / 1e6;

        return new BenchResult(
            runner: 'stream_select',
            wallTimeMs: $wall,
            busyTimeMs: $busy,
            idleTimeMs: max(0, $wall - $busy),
            tasksCompleted: $completed,
            thanksIncluded: $thanks,
            totalCost: $cost,
            totalValue: $value,
            totalMessages: $messages,
            extra: ['mux' => 'stream_select'],
        );
    }
}
