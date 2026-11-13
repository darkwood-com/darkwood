<?php

namespace App\Tests\Integration\Navi;

use App\Integration\Navi\NullNaviTraceClient;
use PHPUnit\Framework\TestCase;

final class NullNaviTraceClientTest extends TestCase
{
    public function testReturnsLocalTraceId(): void
    {
        $client = new NullNaviTraceClient();
        $traceId = $client->startOrAttachTrace('run-1', []);
        $client->recordStep($traceId, 'step');
        $client->finishTrace($traceId);

        self::assertSame('local-trace-run-1', $traceId);
    }
}
