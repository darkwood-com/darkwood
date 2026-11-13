<?php

declare(strict_types=1);

namespace App\Tests;

use App\AppFactory;
use App\Http\JsonErrorHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use React\Http\Message\ServerRequest;
use function React\Async\await;

final class IntegrationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = dirname(__DIR__);
    }

    public function testHealthEndpoint(): void
    {
        $app = AppFactory::create($this->root);
        $response = $app(new ServerRequest('GET', 'http://localhost/health'));
        self::assertSame(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('ok', $data['status']);
        self::assertSame('framework-x', $data['runtime']);
        self::assertSame('darkwood/flow', $data['orchestrator']);
    }

    public function testWorkflowRunCompletesAndPersists(): void
    {
        $app = AppFactory::create($this->root);
        $request = new ServerRequest(
            'POST',
            'http://localhost/workflows/change-review/runs',
            ['Content-Type' => 'application/json'],
            json_encode([
                'changeId' => 'CHANGE-TEST',
                'title' => 'Test run',
                'files' => ['src/AppFactory.php'],
                'risk' => 'low',
                'qualityMode' => 'sequential',
            ], JSON_THROW_ON_ERROR)
        );

        $response = $app($request);
        if (!$response instanceof ResponseInterface) {
            $response = await($response);
        }

        self::assertSame(201, $response->getStatusCode());
        $report = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('completed', $report['status']);
        self::assertNotEmpty($report['runId']);
        self::assertFileExists($this->root . '/var/runs/' . $report['runId'] . '.json');

        $get = $app(new ServerRequest('GET', 'http://localhost/workflows/change-review/runs/' . $report['runId']));
        self::assertSame(200, $get->getStatusCode());
    }

    public function testJsonErrorHandlerDoesNotLeakPaths(): void
    {
        $handler = new JsonErrorHandler($this->root . '/var/log/test-error.log');
        $response = $handler(
            new ServerRequest('GET', 'http://localhost/boom'),
            static function (): never {
                throw new \RuntimeException('secret path /Users/someone/secret.key leaked');
            }
        );

        self::assertSame(500, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('internal_error', $body);
        self::assertStringNotContainsString('/Users/someone/secret.key', $body);
        self::assertStringNotContainsString('RuntimeException', $body);
    }

    public function testInvalidJsonReturnsSafeError(): void
    {
        $app = AppFactory::create($this->root);
        $request = new ServerRequest(
            'POST',
            'http://localhost/workflows/change-review/runs',
            ['Content-Type' => 'application/json'],
            '{not-json'
        );
        $response = $app($request);
        self::assertSame(400, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('invalid_json', $data['error']['code']);
    }
}
