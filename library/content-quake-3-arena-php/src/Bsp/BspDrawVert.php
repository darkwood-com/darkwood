<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * drawVert_t — 44 bytes (qfiles.h).
 */
final class BspDrawVert
{
    public const SIZE = 44;

    /**
     * @param array{0: float, 1: float, 2: float} $xyz
     * @param array{0: float, 1: float} $st
     * @param array{0: float, 1: float} $lightmap
     * @param array{0: float, 1: float, 2: float} $normal
     * @param array{0: int, 1: int, 2: int, 3: int} $color
     */
    public function __construct(
        public readonly array $xyz,
        public readonly array $st,
        public readonly array $lightmap,
        public readonly array $normal,
        public readonly array $color,
    ) {
    }

    public static function fromBytes(string $bytes, int $offset): self
    {
        return new self(
            xyz: [
                BspBinary::float32($bytes, $offset),
                BspBinary::float32($bytes, $offset + 4),
                BspBinary::float32($bytes, $offset + 8),
            ],
            st: [
                BspBinary::float32($bytes, $offset + 12),
                BspBinary::float32($bytes, $offset + 16),
            ],
            lightmap: [
                BspBinary::float32($bytes, $offset + 20),
                BspBinary::float32($bytes, $offset + 24),
            ],
            normal: [
                BspBinary::float32($bytes, $offset + 28),
                BspBinary::float32($bytes, $offset + 32),
                BspBinary::float32($bytes, $offset + 36),
            ],
            color: [
                ord($bytes[$offset + 40]),
                ord($bytes[$offset + 41]),
                ord($bytes[$offset + 42]),
                ord($bytes[$offset + 43]),
            ],
        );
    }
}
