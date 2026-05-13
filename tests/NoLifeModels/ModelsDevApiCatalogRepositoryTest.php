<?php

declare(strict_types=1);

namespace App\Tests\NoLifeModels;

use App\NoLifeModels\Infrastructure\Catalog\ModelsDevApiCatalogRepository;
use PHPUnit\Framework\TestCase;

final class ModelsDevApiCatalogRepositoryTest extends TestCase
{
    public function testParseDecodedApiFlattensProvidersAndModels(): void
    {
        $fixture = [
            'acme' => [
                'id' => 'acme',
                'name' => 'Acme',
                'models' => [
                    'widget-1' => [
                        'id' => 'widget-1',
                        'name' => 'Widget 1',
                        'family' => 'widgets',
                        'modalities' => [
                            'input' => ['text'],
                            'output' => ['text', 'image'],
                        ],
                        'limit' => ['context' => 8192, 'input' => 4096, 'output' => 2048],
                        'cost' => [
                            'input' => 1.5,
                            'output' => 2.5,
                            'reasoning' => 0.1,
                            'context_over_200k' => ['input' => 3.0, 'output' => 4.0],
                        ],
                        'tool_call' => true,
                        'reasoning' => false,
                        'attachment' => true,
                        'structured_output' => true,
                        'open_weights' => false,
                        'knowledge' => '2025-01',
                        'release_date' => '2026-01-01',
                    ],
                ],
            ],
        ];

        $rows = ModelsDevApiCatalogRepository::parseDecodedApi($fixture);

        self::assertCount(1, $rows);
        $e = $rows[0];
        self::assertSame('acme/widget-1', $e->id->value);
        self::assertSame('acme', $e->providerId->value);
        self::assertSame('Widget 1', $e->name);
        self::assertSame(8192, $e->contextLength);
        self::assertSame(['text'], $e->inputModalities);
        self::assertSame(['text', 'image'], $e->outputModalities);
        self::assertSame(['tool_call', 'attachment'], $e->capabilities);
        self::assertSame('widgets', $e->family);
        self::assertSame(1.5, $e->costPerMillionInput);
        self::assertSame(2.5, $e->costPerMillionOutput);
        self::assertSame(0.1, $e->costPerMillionReasoning);
        self::assertSame(3.0, $e->costPerMillionOver200kInput);
        self::assertSame(4.0, $e->costPerMillionOver200kOutput);
        self::assertSame(4096, $e->limitMaxInput);
        self::assertSame(2048, $e->limitMaxOutput);
        self::assertFalse($e->reasoningEnabled);
        self::assertTrue($e->toolCallEnabled);
        self::assertTrue($e->structuredOutput);
        self::assertFalse($e->openWeights);
        self::assertSame('2025-01', $e->knowledge);
        self::assertSame('2026-01-01', $e->releaseDate);
    }

    public function testJsonCacheCodecRoundTrip(): void
    {
        $fixture = [
            'acme' => [
                'id' => 'acme',
                'name' => 'Acme',
                'models' => [
                    'widget-1' => [
                        'id' => 'widget-1',
                        'name' => 'Widget 1',
                        'family' => 'widgets',
                        'modalities' => [
                            'input' => ['text'],
                            'output' => ['text', 'image'],
                        ],
                        'limit' => ['context' => 8192],
                        'tool_call' => true,
                        'reasoning' => false,
                        'attachment' => true,
                        'structured_output' => false,
                    ],
                ],
            ],
        ];

        $entries = ModelsDevApiCatalogRepository::parseDecodedApi($fixture);
        self::assertCount(1, $entries);

        $ref = new \ReflectionClass(ModelsDevApiCatalogRepository::class);
        $encode = $ref->getMethod('encodeCachedCatalog');
        $decode = $ref->getMethod('decodeCachedCatalogBlob');

        $blob = $encode->invoke(null, $entries);
        self::assertStringContainsString('"v":3', $blob);

        $back = $decode->invoke(null, $blob);
        self::assertCount(1, $back);
        self::assertSame('acme/widget-1', $back[0]->id->value);
        self::assertFalse($back[0]->structuredOutput);
    }

    public function testParseDecodedApiSkipsNonProviderShapes(): void
    {
        $rows = ModelsDevApiCatalogRepository::parseDecodedApi([
            'noise' => 'x',
            'empty' => ['id' => 'empty', 'models' => []],
        ]);

        self::assertSame([], $rows);
    }
}
