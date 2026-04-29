<?php

namespace App\Tests\Integration\Ai;

use App\Agent\Builtin\CeoAgent;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Integration\Ai\AiAgentRequest;
use App\Integration\Ai\SymfonyAiAgentClient;
use PHPUnit\Framework\TestCase;

final class SymfonyAiAgentClientTest extends TestCase
{
    public function testUsesConfiguredAgentServiceAndResponseFormat(): void
    {
        $fakeAgent = new class {
            /** @var array<string, mixed> */
            public array $lastOptions = [];
            public function call(object $messages, array $options): object
            {
                $this->lastOptions = $options;

                return new class {
                    public function getContent(): array
                    {
                        return ['title' => 'Acme landing page'];
                    }
                };
            }
        };

        $client = new SymfonyAiAgentClient(['ceo' => $fakeAgent]);
        $request = AiAgentRequest::fromAgentExecution(
            new CeoAgent(),
            new AgentInput(['brief' => 'x'], ['response_format' => 'App\\Integration\\Ai\\Structured\\WebsiteBrief']),
            new AgentContext('run-1', 'tester'),
        );

        $response = $client->run($request);

        self::assertSame('symfony_ai', $response->metadata['source']);
        self::assertArrayHasKey('response_format', $fakeAgent->lastOptions);
    }

    public function testFallsBackToDomainAgentWhenSymfonyAiServiceIsNotConfigured(): void
    {
        $client = new SymfonyAiAgentClient();

        $response = $client->run(AiAgentRequest::fromAgentExecution(
            new CeoAgent(),
            new AgentInput(['brief' => 'demo']),
            new AgentContext('run-1', 'tester', 'nolife', 'local-trace-run-1'),
        ));

        self::assertSame('ceo', $response->payload['agent']);
        self::assertSame('builtin', $response->metadata['source']);
    }

    public function testThrowsWhenServiceMissingAndFallbackDisabled(): void
    {
        $client = new SymfonyAiAgentClient();
        $this->expectException(\RuntimeException::class);
        $request = AiAgentRequest::fromAgentExecution(
            new CeoAgent(),
            new AgentInput(['brief' => 'demo'], ['allow_ai_fallback' => false]),
            new AgentContext('run-1', 'tester'),
        );
        $client->run($request);
    }

    public function testRequestIncludesAgentPromptAndPayload(): void
    {
        $request = AiAgentRequest::fromAgentExecution(
            new CeoAgent(),
            new AgentInput(['brief' => 'demo']),
            new AgentContext('run-2', 'tester'),
        );

        self::assertStringContainsString('CEO Agent', $request->systemPrompt);
        self::assertStringContainsString('"brief": "demo"', $request->userPrompt);
    }

    public function testNormalizesTextAndObjectResults(): void
    {
        $textAgent = new class {
            public function call(object $messages, array $options): object
            {
                return new class {
                    public function getContent(): string
                    {
                        return 'hello';
                    }
                };
            }
        };
        $objectAgent = new class {
            public function call(object $messages, array $options): object
            {
                return new class {
                    public function getContent(): object
                    {
                        return new class {
                            public string $title = 'Acme';
                        };
                    }
                };
            }
        };

        $request = AiAgentRequest::fromAgentExecution(
            new CeoAgent(),
            new AgentInput(['brief' => 'demo']),
            new AgentContext('run-2', 'tester'),
        );

        $textResponse = (new SymfonyAiAgentClient(['ceo' => $textAgent]))->run($request);
        self::assertSame('hello', $textResponse->payload['content']);

        $objectResponse = (new SymfonyAiAgentClient(['ceo' => $objectAgent]))->run($request);
        self::assertIsArray($objectResponse->payload['content']);
        self::assertSame('Acme', $objectResponse->payload['content']['title']);
    }
}
