<?php

declare(strict_types=1);

namespace App\Tests\NoLifeModels;

use App\NoLifeModels\Infrastructure\Catalog\CatalogModelPayloadMapper;
use PHPUnit\Framework\TestCase;

final class CatalogModelPayloadMapperTest extends TestCase
{
    public function testResolvedContextLengthPrefersExplicitContextLength(): void
    {
        self::assertSame(
            99,
            CatalogModelPayloadMapper::resolvedContextLength([
                'contextLength' => 99,
                'limit' => ['context' => 1],
            ]),
        );
    }

    public function testResolvedContextLengthFallsBackToLimitContext(): void
    {
        self::assertSame(
            128_000,
            CatalogModelPayloadMapper::resolvedContextLength([
                'limit' => ['context' => 128_000],
            ]),
        );
    }
}
