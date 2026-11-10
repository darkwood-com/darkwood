<?php

declare(strict_types=1);

namespace App\Tests;

use App\Service\MemoryExamples;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class FlowLibraryTest extends KernelTestCase
{
    public function testActualInstalledFlowCarriesAComputedResultWithoutPersistentState(): void
    {
        self::bootKernel();
        $examples = self::getContainer()->get(MemoryExamples::class);
        $first = $examples->flowLibrary();
        self::assertSame(1, $first['result']['calculations']);
        self::assertSame([4, 7, 9], $first['result']['result']['values']);
        self::assertSame(20, $first['result']['result']['total']);
        self::assertEqualsWithDelta(20 / 3, $first['result']['result']['mean'], 0.00001);
        self::assertSame(80, $first['result']['result']['remaining']);
        self::assertSame($first, $examples->flowLibrary());
        self::assertTrue(class_exists(\Flow\FlowFactory::class));
    }
}
