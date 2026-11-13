<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Execution\LambdaExecutionContext;
use App\Domain\Primitive\PrimitiveInvocation;
use App\Infrastructure\Composio\ComposioClient;
use App\Infrastructure\Composio\ComposioPrimitiveInvoker;
use App\Infrastructure\Composio\ConnectedAccountResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ComposioPrimitiveInvokerTest extends TestCase
{
    public function testInvokesMappedTool(): void
    {
        $http = new MockHttpClient([
            new MockResponse(json_encode([
                'name' => 'DEMO_ACTION',
                'toolkit' => ['slug' => 'demo'],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'items' => [
                    [
                        'id' => 'conn-99',
                        'toolkit' => ['slug' => 'demo'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'successful' => true,
                'data' => ['message' => 'ok'],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $api = new ComposioClient($http, 'test-key', 'https://backend.test/api');
        $resolver = new ConnectedAccountResolver($api);
        $invoker = new ComposioPrimitiveInvoker($api, $resolver, ['send_email' => 'DEMO_ACTION']);

        $ctx = new LambdaExecutionContext('entity-1', ['q' => 'hi']);
        $out = $invoker->invoke(new PrimitiveInvocation('send_email', []), $ctx);

        self::assertSame(['message' => 'ok'], $out['data'] ?? null);
    }
}
