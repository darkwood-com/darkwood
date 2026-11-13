<?php

declare(strict_types=1);

namespace App\Flow\IO;

/**
 * PHP 8.6 Io\Poll\* backend. Falls back to StreamSelectPoller when unavailable.
 */
final class NativePollPoller implements PollerInterface
{
    private readonly PollerInterface $fallback;

    public function __construct(?PollerInterface $fallback = null)
    {
        $this->fallback = $fallback ?? new StreamSelectPoller();
    }

    public function isNativeAvailable(): bool
    {
        return class_exists(\Io\Poll\Context::class)
            && class_exists(\Io\Poll\StreamPollHandle::class);
    }

    public function name(): string
    {
        return $this->isNativeAvailable() ? 'io_poll' : 'stream_select_fallback';
    }

    public function poll(array $read, array $write, float $timeoutSeconds): array
    {
        if (!$this->isNativeAvailable()) {
            return $this->fallback->poll($read, $write, $timeoutSeconds);
        }

        $context = new \Io\Poll\Context();
        $map = [];

        foreach ($read as $stream) {
            $handle = new \Io\Poll\StreamPollHandle($stream);
            $context->add($handle, \Io\Poll\Event::Read);
            $map[spl_object_id($handle)] = ['stream' => $stream, 'mode' => 'read'];
        }
        foreach ($write as $stream) {
            $handle = new \Io\Poll\StreamPollHandle($stream);
            $context->add($handle, \Io\Poll\Event::Write);
            $map[spl_object_id($handle)] = ['stream' => $stream, 'mode' => 'write'];
        }

        $timeout = class_exists(\Time\Duration::class)
            ? \Time\Duration::fromTotalMicroseconds((int) ($timeoutSeconds * 1_000_000))
            : $timeoutSeconds;

        $events = $context->wait($timeout);
        $readyRead = [];
        $readyWrite = [];

        if (!is_iterable($events)) {
            return [[], []];
        }

        foreach ($events as $event) {
            foreach ($map as $meta) {
                if ('read' === $meta['mode']) {
                    $readyRead[] = $meta['stream'];
                } else {
                    $readyWrite[] = $meta['stream'];
                }
            }
            break;
        }

        return [array_values(array_unique($readyRead)), array_values(array_unique($readyWrite))];
    }
}
