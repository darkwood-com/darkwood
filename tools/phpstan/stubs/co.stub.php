<?php

declare(strict_types=1);

/**
 * OpenSwoole global co class stub for PHPStan.
 *
 * This is a global class provided by the OpenSwoole extension.
 * It provides static methods for coroutine operations.
 */
final class co
{
    /**
     * Run a coroutine.
     *
     * @return mixed
     */
    public static function run(callable $callback) {}

    /**
     * Sleep for the specified number of seconds.
     */
    public static function sleep(int $seconds): bool {}
}
