<?php

namespace App\Tests\Integration\Ai;

use App\Agent\Builtin\CeoAgent;
use App\Agent\Runtime\AgentContext;
use App\Agent\Runtime\AgentInput;
use App\Integration\Ai\AiAgentRequest;
use App\Integration\Ai\NullAiAgentClient;
use PHPUnit\Framework\TestCase;

final class NullAiAgentClientTest extends TestCase
{
    public function testFallsBackToAgentExecution(): void
    {
        $client = new NullAiAgentClient();
        $response = $client->run(new AiAgentRequest(
            agent: new CeoAgent(),
            input: new AgentInput(['brief' => 'x']),
            context: new AgentContext('run-1', 'tester'),
            systemPrompt: 'system',
            userPrompt: 'user',
        ));

        self::assertSame('ceo', $response->payload['agent']);
    }
}
