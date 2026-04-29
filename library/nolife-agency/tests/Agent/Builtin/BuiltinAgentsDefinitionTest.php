<?php

namespace App\Tests\Agent\Builtin;

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
use PHPUnit\Framework\TestCase;

final class BuiltinAgentsDefinitionTest extends TestCase
{
    public function testAllBuiltinAgentsExposeRoleResponsibilitiesAndCapabilities(): void
    {
        $agents = [
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
        ];

        foreach ($agents as $agent) {
            $definition = $agent->definition();
            self::assertNotSame('', $definition->role);
            self::assertNotEmpty($definition->responsibilities);
            self::assertNotEmpty($definition->capabilities);
        }
    }
}
