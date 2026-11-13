<?php

declare(strict_types=1);

namespace App\Flow;

use Flow\FlowInterface;
use Flow\Ip;
use React\Promise\PromiseInterface;

/**
 * Runs a darkwood/flow graph under Framework X without stopping the shared ReactPHP loop.
 */
final class EmbeddedReactFlowRunner
{
    public function __construct(
        private readonly EmbeddedReactDriver $driver,
    ) {
    }

    /**
     * @template T
     *
     * @param Ip<T> $ip
     *
     * @return PromiseInterface<T>
     */
    public function run(FlowInterface $flow, Ip $ip): PromiseInterface
    {
        $data = $ip->data;
        $done = $this->driver->whenComplete();
        $flow($ip);
        $flow->await();

        return $done->then(static fn () => $data);
    }
}
