<?php

declare(strict_types=1);

namespace App\Tests\Flow\IO;

use App\Flow\IO\Duration;
use App\Flow\IO\NativePollPoller;
use App\Flow\IO\StreamSelectPoller;
use PHPUnit\Framework\TestCase;

final class PollerTest extends TestCase
{
    public function testStreamSelectPollerName(): void
    {
        $poller = new StreamSelectPoller();
        self::assertSame('stream_select', $poller->name());
    }

    public function testNativePollFallsBackOnPhp85(): void
    {
        $poller = new NativePollPoller();
        if ($poller->isNativeAvailable()) {
            self::assertSame('io_poll', $poller->name());
        } else {
            self::assertSame('stream_select_fallback', $poller->name());
        }

        $result = $poller->poll([], [], 0.01);
        self::assertSame([[], []], $result);
    }

    public function testDurationHelpers(): void
    {
        $d = Duration::fromMilliseconds(1500);
        self::assertEqualsWithDelta(1.5, $d->toSeconds(), 0.0001);
        self::assertSame(1, $d->compare(Duration::fromSeconds(1.0)));
        self::assertEqualsWithDelta(3.0, $d->multiply(2)->toSeconds(), 0.0001);
    }
}
