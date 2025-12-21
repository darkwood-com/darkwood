<?php

declare(strict_types=1);

namespace OpenSwoole;

/**
 * Timer class stub for PHPStan.
 */
final class Timer
{
    /**
     * @param mixed ...$params
     */
    public static function tick(int $ms, callable $callback, ...$params): false|int {}

    public static function clear(int $timerId): bool {}
}
