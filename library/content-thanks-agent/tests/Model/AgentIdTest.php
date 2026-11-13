<?php

declare(strict_types=1);

namespace App\Tests\Model;

use App\Model\AgentId;
use PHPUnit\Framework\TestCase;

final class AgentIdTest extends TestCase
{
    public function testThanksProducesZeroValue(): void
    {
        self::assertSame(0, AgentId::Thanks->valueWeight());
        self::assertGreaterThan(0, AgentId::Github->valueWeight());
    }

    public function testUsefulAgentsExcludeThanksAndSummary(): void
    {
        $ids = array_map(static fn (AgentId $id) => $id->value, AgentId::usefulAgents());

        self::assertNotContains(AgentId::Thanks->value, $ids);
        self::assertNotContains(AgentId::Summary->value, $ids);
        self::assertCount(5, $ids);
    }
}
