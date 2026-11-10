<?php

declare(strict_types=1);

namespace App\Study;

use Closure;
use Flow\Driver\FiberDriver;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\JobInterface;

final class DecidePipeline
{
    /**
     * Same Flow graph every time: normalize → decide job → collect.
     * The decide job is the substitutable unit (Rule*Job vs Jev*Job).
     */
    public static function run(mixed $input, Closure|JobInterface $decide): Payload
    {
        $box = new \stdClass();
        $box->value = null;
        $driver = new FiberDriver();

        $flow = (new FlowFactory($driver))->create(static function () use ($decide, $box) {
            yield static fn (mixed $in): Payload => $in instanceof Payload ? $in : Payload::fromInput($in);
            yield $decide;
            yield static function (Payload $payload) use ($box): Payload {
                $box->value = $payload;

                return $payload;
            };
        });

        $flow(new Ip($input));
        $flow->await();

        if (!$box->value instanceof Payload) {
            throw new \RuntimeException('Flow did not collect a Payload.');
        }

        return $box->value;
    }
}
