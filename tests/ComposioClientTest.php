<?php

declare(strict_types=1);

namespace App\Tests;

use App\Infrastructure\Composio\ComposioClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ComposioClientTest extends TestCase
{
    public function testGetToolSendsApiKey(): void
    {
        $http = new MockHttpClient([
            function (string $method, string $url, array $options): MockResponse {
                self::assertSame('GET', $method);
                self::assertStringContainsString('/v3/tools/GMAIL_TEST', $url);
                $headers = $options['normalized_headers'] ?? [];
                self::assertArrayHasKey('x-api-key', $headers);
                self::assertStringContainsString('secret', $headers['x-api-key'][0] ?? '');

                return new MockResponse(json_encode(['name' => 'GMAIL_TEST', 'toolkit' => ['slug' => 'gmail']], JSON_THROW_ON_ERROR));
            },
        ]);

        $client = new ComposioClient($http, 'secret', 'https://api.example/api');
        $data = $client->getTool('GMAIL_TEST');

        self::assertSame('GMAIL_TEST', $data['name']);
    }

    public function testExecuteToolPostsJsonPayload(): void
    {
        $http = new MockHttpClient([
            function (string $method, string $url, array $options): MockResponse {
                self::assertSame('POST', $method);
                self::assertStringContainsString('/v3/tools/execute/ACTION', $url);
                $payload = json_decode($options['body'] ?? '', true, 512, JSON_THROW_ON_ERROR);
                self::assertSame(
                    ['connected_account_id' => 'ca1', 'arguments' => ['a' => 1], 'user_id' => 'user-1'],
                    $payload
                );

                return new MockResponse(json_encode(['data' => ['x' => 1]], JSON_THROW_ON_ERROR));
            },
        ]);

        $client = new ComposioClient($http, 'k', 'https://api.example/api');
        $out = $client->executeTool('ACTION', 'ca1', ['a' => 1], 'user-1');

        self::assertSame(['x' => 1], $out['data']);
    }
}
