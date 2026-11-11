<?php

declare(strict_types=1);

namespace Flow\Driver;

use Closure;
use Flow\DriverInterface;
use Flow\Exception;
use RuntimeException;
use Spatie\Async\Pool;
use Throwable;

class SpatieDriver implements DriverInterface
{
    private Pool $pool;

    public function __construct()
    {
        if (!class_exists('Spatie\\Async\\Pool')) {
            throw new RuntimeException('Spatie Async is not loaded. Suggest install it with composer require spatie/async');
        }

        $this->pool = Pool::create();
    }

    public function async(Closure $callback, Closure $onResolve = null): Closure
    {
        return function (...$args) use ($callback, $onResolve): void {
            $this->pool->add(static function () use ($callback, $onResolve, $args) {
                try {
                    $return = $callback(...$args, ...($args = []));
                    if ($onResolve) {
                        $onResolve($return);
                    }
                } catch (Throwable $exception) {
                    if ($onResolve) {
                        $onResolve(new Exception($exception->getMessage(), $exception->getCode(), $exception));
                    }
                }
            });
        };
    }

    public function delay(float $seconds): void
    {
        delay($seconds);
    }

    public function tick(int $interval, Closure $callback): Closure
    {
        $tickId = $this->eventLoop->addPeriodicTimer($interval, $callback);

        return function () use ($tickId) {
            $this->eventLoop->cancelTimer($tickId);
        };
    }
}
