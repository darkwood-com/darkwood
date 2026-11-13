<?php

declare(strict_types=1);

namespace App\Flow;

use Closure;
use Flow\DriverInterface;
use Flow\Driver\DriverTrait;
use Flow\Event;
use Flow\Event\AsyncEvent;
use Flow\Event\PopEvent;
use Flow\Event\PullEvent;
use Flow\Event\PushEvent;
use Flow\Exception\RuntimeException;
use Flow\Ip;
use Flow\JobInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use Throwable;

use function array_key_exists;
use function React\Async\async;
use function React\Async\delay;

/**
 * ReactPHP Flow driver that does NOT own the event loop.
 *
 * Native Flow\Driver\ReactDriver::await() calls Loop::run() / Loop::stop(), which
 * conflicts with Framework X's long-running HttpServerRunner. This adapter keeps
 * the same async/defer/delay semantics but schedules work with futureTick and
 * resolves a Promise when the Ip stream is drained.
 *
 * This is an integration adapter, not upstream darkwood/flow API.
 *
 * @template TArgs
 * @template TReturn
 *
 * @implements DriverInterface<TArgs,TReturn>
 */
final class EmbeddedReactDriver implements DriverInterface
{
    use DriverTrait;

    private int $ticks = 0;

    private LoopInterface $eventLoop;

    /** @var Deferred<null>|null */
    private ?Deferred $completion = null;

    public function __construct(?LoopInterface $eventLoop = null)
    {
        $this->eventLoop = $eventLoop ?? Loop::get();
    }

    public function async(Closure|JobInterface $callback): Closure
    {
        return static function (...$args) use ($callback) {
            return async(static function () use ($callback, $args) {
                try {
                    return $callback(...$args, ...($args = []));
                } catch (Throwable $exception) {
                    return new RuntimeException($exception->getMessage(), $exception->getCode(), $exception);
                }
            })();
        };
    }

    /**
     * @return Promise<TReturn>
     */
    public function defer(Closure $callback): Promise
    {
        $deferred = new Deferred();

        try {
            $callback(static function ($return) use ($deferred): void {
                $deferred->resolve($return);
            }, static function ($fn, $next): void {
                $fn($next);
            });
        } catch (Throwable $exception) {
            $deferred->resolve(new RuntimeException($exception->getMessage(), $exception->getCode(), $exception));
        }

        return $deferred->promise();
    }

    /**
     * Schedules Flow processing on the shared loop. Does not call Loop::run()/stop().
     * Pair with {@see whenComplete()} before invoking Flow::await().
     */
    public function await(array &$stream): void
    {
        $async = function (Closure|JobInterface $job) {
            return function (mixed $data) use ($job) {
                $async = $this->async($job);
                $promise = $async($data);

                return static function ($then) use ($promise): void {
                    $promise->then($then);
                };
            };
        };

        $defer = function (Closure|JobInterface $job) {
            return function ($then) use ($job): void {
                $promise = $this->defer($job);
                $promise->then($then);
            };
        };

        $loop = function () use (&$loop, &$stream, $async, $defer): void {
            foreach ($stream['dispatchers'] as $index => $dispatcher) {
                $nextIps = $dispatcher->dispatch(new PullEvent(), Event::PULL)->getIps();
                foreach ($nextIps as $nextIp) {
                    $job = $stream['fnFlows'][$index]['job'];

                    $stream['dispatchers'][$index]->dispatch(new AsyncEvent($async, $defer, $job, $nextIp, function ($data) use (&$stream, $index, $nextIp): void {
                        if ($data instanceof RuntimeException && array_key_exists($index, $stream['fnFlows']) && $stream['fnFlows'][$index]['errorJob'] !== null) {
                            $stream['fnFlows'][$index]['errorJob']($data);
                        } elseif (array_key_exists($index + 1, $stream['fnFlows'])) {
                            $ip = new Ip($data);
                            $stream['dispatchers'][$index + 1]->dispatch(new PushEvent($ip), Event::PUSH);
                        }

                        $stream['dispatchers'][$index]->dispatch(new PopEvent($nextIp), Event::POP);
                    }), Event::ASYNC);
                }
            }

            if ($this->countIps($stream['dispatchers']) > 0 || $this->ticks > 0) {
                $this->eventLoop->futureTick($loop);
            } elseif ($this->completion !== null) {
                $completion = $this->completion;
                $this->completion = null;
                $completion->resolve(null);
            }
        };

        $this->eventLoop->futureTick($loop);
    }

    /**
     * @return PromiseInterface<null>
     */
    public function whenComplete(): PromiseInterface
    {
        $this->completion = new Deferred();

        return $this->completion->promise();
    }

    public function delay(float $seconds): void
    {
        delay($seconds);
    }

    public function tick($interval, Closure $callback): Closure
    {
        $this->ticks++;
        $timer = $this->eventLoop->addPeriodicTimer($interval, $callback);

        return function () use ($timer): void {
            $this->eventLoop->cancelTimer($timer);
            $this->ticks--;
        };
    }
}
