<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\RunStreamChunkNormalizer;
use PHPUnit\Framework\TestCase;

final class RunStreamChunkNormalizerTest extends TestCase
{
    public function testItKeepsRenderableFrameChunksUnchanged(): void
    {
        $chunk = [
            'frame' => 3,
            'size' => 2,
            'cells' => [[1, 0], [0, 1]],
        ];

        self::assertSame($chunk, $this->normalizer()->normalize($chunk));
    }

    public function testItNormalizesTrainingChunksWithBestFrame(): void
    {
        $normalized = $this->normalizer()->normalize([
            'generation' => 2,
            'best_fitness' => 0.75,
            'average_fitness' => 0.5,
            'seed' => 42,
            'dataset' => 'beacon',
            'best_program' => ['type' => 'sequence', 'nodes' => [['type' => 'next']]],
            'best_frame' => [
                'frame' => 6,
                'size' => 2,
                'cells' => [[0, 1], [1, 0]],
            ],
        ]);

        self::assertSame(6, $normalized['frame']);
        self::assertSame([[0, 1], [1, 0]], $normalized['cells']);
        self::assertSame([
            'fitness' => 0.75,
            'average_fitness' => 0.5,
            'generation' => 2,
            'seed' => 42,
            'mode' => 'supervised',
            'dataset' => 'beacon',
            'best_program' => ['type' => 'sequence', 'nodes' => [['type' => 'next']]],
        ], $normalized['metadata']);
    }

    public function testItFallsBackToMetadataOnlyPlaceholder(): void
    {
        $normalized = $this->normalizer()->normalize([
            'generation' => 4,
            'best_fitness' => 0.5,
            'seed' => 99,
        ]);

        self::assertSame(4, $normalized['frame']);
        self::assertSame(1, $normalized['size']);
        self::assertSame([[0]], $normalized['cells']);
        self::assertSame([
            'fitness' => 0.5,
            'generation' => 4,
            'seed' => 99,
        ], $normalized['metadata']);
    }

    public function testItInfersUnsupervisedModeWhenBestFrameIsPresent(): void
    {
        $normalized = $this->normalizer()->normalize([
            'generation' => 1,
            'best_fitness' => 0.25,
            'best_frame' => [
                'frame' => 1,
                'size' => 1,
                'cells' => [[1]],
            ],
        ]);

        self::assertSame('unsupervised', $normalized['metadata']['mode']);
    }

    private function normalizer(): RunStreamChunkNormalizer
    {
        return new RunStreamChunkNormalizer();
    }
}
