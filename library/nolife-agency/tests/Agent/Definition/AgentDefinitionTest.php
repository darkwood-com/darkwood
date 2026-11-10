<?php

namespace App\Tests\Agent\Definition;

use App\Agent\Definition\AgentCapability;
use App\Agent\Definition\AgentDefinition;
use PHPUnit\Framework\TestCase;

final class AgentDefinitionTest extends TestCase
{
    public function testStoresDefinitionFields(): void
    {
        $definition = new AgentDefinition(
            key: 'ceo',
            name: 'CEO Agent',
            role: 'Role',
            description: 'Desc',
            responsibilities: ['One'],
            capabilities: [new AgentCapability('strategy')],
        );

        self::assertSame('ceo', $definition->key);
        self::assertCount(1, $definition->capabilities);
    }
}
