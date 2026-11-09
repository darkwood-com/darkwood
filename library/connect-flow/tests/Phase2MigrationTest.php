<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Capability\LambdaCapability;
use App\Domain\Capability\LanguageCapability;
use App\Domain\Lambda\LambdaExpression;
use PHPUnit\Framework\TestCase;

final class Phase2MigrationTest extends TestCase
{
    public function testComposioToolCapabilityRemovedFromDomain(): void
    {
        self::assertFalse(class_exists('App\\Domain\\Capability\\ComposioToolCapability'));
    }

    public function testLambdaFirstDomainTypesExist(): void
    {
        $c = new LambdaCapability(new LambdaExpression('λx.x'));
        self::assertSame('λx.x', $c->defaultProgram()?->source());

        self::assertTrue(class_exists(LanguageCapability::class));
    }

    public function testLocalLambdaParserWasNotKeptInConnectFlow(): void
    {
        self::assertFalse(class_exists('App\\Domain\\Lambda\\LambdaParser'));
    }
}
