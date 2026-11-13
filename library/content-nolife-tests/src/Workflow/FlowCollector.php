<?php

declare(strict_types=1);

namespace App\Workflow;

use Flow\FlowInterface;
use Flow\Ip;
use stdClass;

/**
 * Observes the final job payload without pretending Ip is mutable.
 * Flow wraps each stage result in a new Ip; the original Ip stays unchanged.
 */
final class FlowCollector
{
    /**
     * @template T
     *
     * @param FlowInterface<mixed> $flow
     * @param Ip<mixed>            $ip
     *
     * @return T
     */
    public static function run(FlowInterface $flow, Ip $ip): mixed
    {
        $box = new stdClass();
        $box->value = null;

        $flow->fn(static function (mixed $data) use ($box): mixed {
            $box->value = $data;

            return $data;
        });

        ($flow)($ip);
        $flow->await();

        return $box->value;
    }
}
