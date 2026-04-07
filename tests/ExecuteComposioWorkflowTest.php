<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Workflow\WorkflowRunner;
use App\Domain\Capability\ComposioToolCapability;
use App\Domain\Execution\Context;
use App\Infrastructure\Action\ExecuteComposioToolAction;
use App\Infrastructure\Composio\ComposioClient;
use App\Infrastructure\Composio\ConnectedAccountResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ExecuteComposioWorkflowTest extends TestCase
{
    public function testWorkflowRunsComposioActionWithResolvedAccount(): void
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
        $capability = new ComposioToolCapability('DEMO_ACTION');
        $step = new ExecuteComposioToolAction($capability, $api, $resolver);

        $runner = new WorkflowRunner();
        $result = $runner->run(
            Context::fromArray([
                'composio_user_id' => 'entity-1',
                'composio_input' => ['q' => 'hi'],
            ]),
            [$step]
        );

        self::assertSame(
            ['message' => 'ok'],
            $result->state()->context()->get('composio_result')['data'] ?? null
        );
        self::assertCount(1, $result->state()->completedActions());
    }

    public function testExplicitConnectedAccountSkipsList(): void
    {
        $http = new MockHttpClient([
            new MockResponse(json_encode([
                'name' => 'DEMO_ACTION',
                'toolkit' => ['slug' => 'demo'],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'successful' => true,
                'data' => [],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $api = new ComposioClient($http, 'test-key', 'https://backend.test/api');
        $resolver = new ConnectedAccountResolver($api);
        $capability = new ComposioToolCapability('DEMO_ACTION');
        $step = new ExecuteComposioToolAction($capability, $api, $resolver);

        $runner = new WorkflowRunner();
        $result = $runner->run(
            Context::fromArray([
                'composio_user_id' => 'entity-1',
                'composio_input' => [],
                'composio_connected_account_id' => 'fixed-id',
            ]),
            [$step]
        );

        self::assertSame([], $result->state()->context()->get('composio_result')['data'] ?? null);
    }

    public function testMultipleCommaSeparatedToolNamesExecutesEachTool(): void
    {
        $http = new MockHttpClient([
            new MockResponse(json_encode([
                'name' => 'DEMO_A',
                'toolkit' => ['slug' => 'demo'],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'items' => [['id' => 'conn-a', 'toolkit' => ['slug' => 'demo']]],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['data' => ['a' => 1]], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'name' => 'DEMO_B',
                'toolkit' => ['slug' => 'demo'],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode([
                'items' => [['id' => 'conn-b', 'toolkit' => ['slug' => 'demo']]],
            ], JSON_THROW_ON_ERROR)),
            new MockResponse(json_encode(['data' => ['b' => 2]], JSON_THROW_ON_ERROR)),
        ]);

        $api = new ComposioClient($http, 'test-key', 'https://backend.test/api');
        $resolver = new ConnectedAccountResolver($api);
        $capability = new ComposioToolCapability('DEMO_A,DEMO_B');
        $step = new ExecuteComposioToolAction($capability, $api, $resolver);

        $runner = new WorkflowRunner();
        $result = $runner->run(
            Context::fromArray([
                'composio_user_id' => 'entity-1',
                'composio_input' => [],
            ]),
            [$step]
        );

        $out = $result->state()->context()->get('composio_result');
        self::assertIsArray($out);
        self::assertSame(['a' => 1], $out['DEMO_A']['data'] ?? null);
        self::assertSame(['b' => 2], $out['DEMO_B']['data'] ?? null);
    }
}

