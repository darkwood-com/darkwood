<?php

declare(strict_types=1);

namespace App\Tests;

use App\Domain\Capability\ComposioToolCapability;
use PHPUnit\Framework\TestCase;

final class ComposioToolCapabilityTest extends TestCase
{
    public function testReturnsConfiguredStringVerbatim(): void
    {
        $c = new ComposioToolCapability(' GMAIL_FETCH_EMAILS , GMAIL_OTHER ');

        self::assertSame(' GMAIL_FETCH_EMAILS , GMAIL_OTHER ', $c->actionName());
    }

    public function testSingleSlugUnchanged(): void
    {
        $c = new ComposioToolCapability('GMAIL_FETCH_EMAILS');

        self::assertSame('GMAIL_FETCH_EMAILS', $c->actionName());
    }
}
