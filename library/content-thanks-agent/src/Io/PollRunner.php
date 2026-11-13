<?php

declare(strict_types=1);

namespace App\Io;

/**
 * PHP 8.6 Io\Poll\* runner — gated when the API is unavailable.
 */
final class PollRunner
{
    public function __construct(
        private readonly DelayedStreamFactory $streams = new DelayedStreamFactory(),
    ) {}

    public function isAvailable(): bool
    {
        return class_exists(\Io\Poll\Context::class)
            && class_exists(\Io\Poll\StreamPollHandle::class);
    }

    /**
     * @param list<array{id: string, delay: float, value: int, messages: int}> $tasks
     */
    public function run(array $tasks): BenchResult
    {
        if (!$this->isAvailable()) {
            return new BenchResult(
                runner: 'php86_poll',
                wallTimeMs: 0,
                busyTimeMs: 0,
                idleTimeMs: 0,
                tasksCompleted: 0,
                thanksIncluded: 0,
                totalCost: 0,
                totalValue: 0,
                totalMessages: 0,
                extra: [
                    'skipped' => true,
                    'reason' => 'Io\\Poll\\Context not available on PHP '.PHP_VERSION,
                    'fallback' => 'Use StreamSelectRunner until PHP 8.6+',
                ],
            );
        }

        $wallStart = hrtime(true);
        $context = new \Io\Poll\Context();
        $handles = [];
        $meta = [];
        $thanks = 0;
        $value = 0;
        $messages = 0;
        $cost = 0.0;
        $busy = 0.0;

        foreach ($tasks as $i => $task) {
            $pair = $this->streams->openPair();
            $handle = new \Io\Poll\StreamPollHandle($pair['read']);
            $context->add($handle, \Io\Poll\Event::Read);
            $handles[$i] = $handle;
            $meta[$i] = [
                'task' => $task,
                'write' => $pair['write'],
                'stream' => $pair['read'],
                'due' => microtime(true) + $task['delay'],
                'written' => false,
                'done' => false,
            ];
        }

        $completed = 0;
        while ($completed < count($tasks)) {
            $now = microtime(true);
            foreach ($meta as &$m) {
                if (!$m['written'] && $now >= $m['due']) {
                    fwrite($m['write'], '1');
                    fclose($m['write']);
                    $m['written'] = true;
                }
            }
            unset($m);

            $timeout = class_exists(\Time\Duration::class)
                ? \Time\Duration::fromMilliseconds(20)
                : 0.02;

            $t0 = hrtime(true);
            $events = $context->wait($timeout);
            $busy += (hrtime(true) - $t0) / 1e6;

            if (!is_iterable($events)) {
                continue;
            }

            foreach ($events as $event) {
                foreach ($handles as $i => $handle) {
                    if ($meta[$i]['done']) {
                        continue;
                    }
                    // Best-effort match: drain corresponding stream when any read fires.
                    if (is_resource($meta[$i]['stream'])) {
                        stream_get_contents($meta[$i]['stream']);
                        fclose($meta[$i]['stream']);
                    }
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
            runner: 'php86_poll',
            wallTimeMs: $wall,
            busyTimeMs: $busy,
            idleTimeMs: max(0, $wall - $busy),
            tasksCompleted: $completed,
            thanksIncluded: $thanks,
            totalCost: $cost,
            totalValue: $value,
            totalMessages: $messages,
            extra: ['mux' => 'Io\\Poll'],
        );
    }
}
