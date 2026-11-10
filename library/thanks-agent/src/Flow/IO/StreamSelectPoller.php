<?php

declare(strict_types=1);

namespace App\Flow\IO;

/**
 * Classic stream_select() backend (PHP ≤8.5 and fallback).
 */
final class StreamSelectPoller implements PollerInterface
{
    public function name(): string
    {
        return 'stream_select';
    }

    public function poll(array $read, array $write, float $timeoutSeconds): array
    {
        $readSocks = $read;
        $writeSocks = $write;
        $except = null;

        if ([] === $readSocks && [] === $writeSocks) {
            return [[], []];
        }

        $sec = (int) $timeoutSeconds;
        $usec = (int) (($timeoutSeconds - $sec) * 1_000_000);
        $n = @stream_select($readSocks, $writeSocks, $except, $sec, $usec);

        if (false === $n || 0 === $n) {
            return [[], []];
        }

        return [$readSocks, $writeSocks];
    }
}
