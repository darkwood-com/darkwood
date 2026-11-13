<?php

namespace App\Tests\Agent\Registry;

use App\Agent\Builtin\ArchitectAgent;
use App\Agent\Builtin\CeoAgent;
use App\Agent\Builtin\CtoAgent;
use App\Agent\Builtin\DeliveryManagerAgent;
use App\Agent\Builtin\DeveloperAgent;
use App\Agent\Builtin\ContentAgent;
use App\Agent\Builtin\MediaMotionAgent;
use App\Agent\Builtin\ProductOwnerAgent;
use App\Agent\Builtin\QaAgent;
use App\Agent\Builtin\SeoGeoSpecialistAgent;
use App\Agent\Registry\ConfiguredAgentRegistry;
use PHPUnit\Framework\TestCase;

final class ConfiguredAgentRegistryTest extends TestCase
{
    private function registry(): ConfiguredAgentRegistry
    {
        return new ConfiguredAgentRegistry([
            new CeoAgent(),
            new CtoAgent(),
            new ProductOwnerAgent(),
            new DeliveryManagerAgent(),
            new ArchitectAgent(),
            new DeveloperAgent(),
            new SeoGeoSpecialistAgent(),
            new ContentAgent(),
            new MediaMotionAgent(),
            new QaAgent(),
        ]);
    }

    public function testReturnsAllConfiguredAgents(): void
    {
        self::assertCount(10, $this->registry()->all());
    }

    public function testResolvesAllExpectedKeys(): void
    {
        $registry = $this->registry();
        foreach (['ceo', 'cto', 'product_owner', 'delivery_manager', 'architect', 'developer', 'seo_geo_specialist', 'content', 'media_motion', 'qa'] as $key) {
            self::assertTrue($registry->has($key));
            self::assertSame($key, $registry->get($key)->definition()->key);
        }
    }

    public function testUnknownAgentThrowsClearException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown agent key');
        $this->registry()->get('unknown');
    }
}
