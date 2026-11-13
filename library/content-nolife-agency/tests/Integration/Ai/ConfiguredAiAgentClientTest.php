<?php

namespace App\Tests\Integration\Ai;

use App\Integration\Ai\ConfiguredAiAgentClient;
use App\Integration\Ai\NullAiAgentClient;
use App\Integration\Ai\SymfonyAiAgentClient;
use PHPUnit\Framework\TestCase;

final class ConfiguredAiAgentClientTest extends TestCase
{
    public function testSelectsNullAdapterByDefault(): void
    {
        $client = new ConfiguredAiAgentClient(null, new SymfonyAiAgentClient(), new NullAiAgentClient());
        self::assertSame('null', $client->name());
        self::assertSame(NullAiAgentClient::class, $client->resolvedClientClass());
    }

    public function testSelectsSymfonyAdapterWhenConfigured(): void
    {
        $client = new ConfiguredAiAgentClient('symfony_ai', new SymfonyAiAgentClient(), new NullAiAgentClient());
        self::assertSame('symfony_ai', $client->name());
        self::assertSame(SymfonyAiAgentClient::class, $client->resolvedClientClass());
    }
}
