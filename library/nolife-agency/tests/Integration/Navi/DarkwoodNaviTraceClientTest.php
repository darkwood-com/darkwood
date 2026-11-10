<?php

namespace App\Tests\Integration\Navi;

use App\Integration\Navi\DarkwoodNaviTraceClient;
use PHPUnit\Framework\TestCase;

final class DarkwoodNaviTraceClientTest extends TestCase
{
    public function testPersistsTraceJson(): void
    {
        $root = sys_get_temp_dir().'/nolife-traces-'.uniqid('', true);
        $client = new DarkwoodNaviTraceClient($root);
        $traceId = $client->startOrAttachTrace('run-9', []);
        $client->recordStep($traceId, 'step.a', ['ok' => true]);
        $client->finishTrace($traceId, ['done' => true]);

        $path = $root.'/run-9/trace.json';
        self::assertFileExists($path);
        $decoded = json_decode((string) file_get_contents($path), true, 512, \JSON_THROW_ON_ERROR);
        self::assertSame('run-9', $decoded['run_id']);
        self::assertSame($traceId, $decoded['trace_id']);
        self::assertSame('completed', $decoded['status']);
        self::assertCount(2, $decoded['events']);
        self::assertSame('step.a', $decoded['events'][0]['name']);
        self::assertSame('trace.finished', $decoded['events'][1]['name']);
    }
}
