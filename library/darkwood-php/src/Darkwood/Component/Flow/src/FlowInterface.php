<?php

declare(strict_types=1);

namespace Flow;

use Closure;

/**
 * https://vkcom.github.io/kphp/kphp-language/static-type-system/generic-functions.html
 * 
 * @template T1 of callable(A):B
 * @template T2 of callable(A):B
 */
interface FlowInterface
{
    public function __invoke(Ip $ip, Closure $callback = null): void;

    /**
     * 
     * @param FlowInterface $flow 
     * @return FlowInterface 
     */
    public function fn(self $flow): self;
}
