<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * dsurface_t — 104 bytes (qfiles.h) + mapSurfaceType_t.
 */
final class BspSurface
{
    public const SIZE = 104;

    public const MST_BAD = 0;
    public const MST_PLANAR = 1;
    public const MST_PATCH = 2;
    public const MST_TRIANGLE_SOUP = 3;
    public const MST_FLARE = 4;

    /**
     * @param array{0: float, 1: float, 2: float} $lightmapOrigin
     * @param list<array{0: float, 1: float, 2: float}> $lightmapVecs
     */
    public function __construct(
        public readonly int $index,
        public readonly int $shaderNum,
        public readonly int $fogNum,
        public readonly int $surfaceType,
        public readonly int $firstVert,
        public readonly int $numVerts,
        public readonly int $firstIndex,
        public readonly int $numIndexes,
        public readonly int $lightmapNum,
        public readonly int $lightmapX,
        public readonly int $lightmapY,
        public readonly int $lightmapWidth,
        public readonly int $lightmapHeight,
        public readonly array $lightmapOrigin,
        public readonly array $lightmapVecs,
        public readonly int $patchWidth,
        public readonly int $patchHeight,
    ) {
    }

    public static function fromBytes(string $bytes, int $offset, int $index): self
    {
        $vecs = [];
        for ($v = 0; $v < 3; $v++) {
            $base = $offset + 60 + $v * 12;
            $vecs[] = [
                BspBinary::float32($bytes, $base),
                BspBinary::float32($bytes, $base + 4),
                BspBinary::float32($bytes, $base + 8),
            ];
        }

        return new self(
            index: $index,
            shaderNum: BspBinary::int32($bytes, $offset),
            fogNum: BspBinary::int32($bytes, $offset + 4),
            surfaceType: BspBinary::int32($bytes, $offset + 8),
            firstVert: BspBinary::int32($bytes, $offset + 12),
            numVerts: BspBinary::int32($bytes, $offset + 16),
            firstIndex: BspBinary::int32($bytes, $offset + 20),
            numIndexes: BspBinary::int32($bytes, $offset + 24),
            lightmapNum: BspBinary::int32($bytes, $offset + 28),
            lightmapX: BspBinary::int32($bytes, $offset + 32),
            lightmapY: BspBinary::int32($bytes, $offset + 36),
            lightmapWidth: BspBinary::int32($bytes, $offset + 40),
            lightmapHeight: BspBinary::int32($bytes, $offset + 44),
            lightmapOrigin: [
                BspBinary::float32($bytes, $offset + 48),
                BspBinary::float32($bytes, $offset + 52),
                BspBinary::float32($bytes, $offset + 56),
            ],
            lightmapVecs: $vecs,
            patchWidth: BspBinary::int32($bytes, $offset + 96),
            patchHeight: BspBinary::int32($bytes, $offset + 100),
        );
    }

    public function typeName(): string
    {
        return match ($this->surfaceType) {
            self::MST_PLANAR => 'planar',
            self::MST_PATCH => 'patch',
            self::MST_TRIANGLE_SOUP => 'triangle_soup',
            self::MST_FLARE => 'flare',
            default => 'bad',
        };
    }
}
