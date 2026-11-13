<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * dshader_t — 72 bytes (qfiles.h).
 */
final class BspShader
{
    public const SIZE = 72;
    public const MAX_QPATH = 64;

    public function __construct(
        public readonly int $index,
        public readonly string $shader,
        public readonly int $surfaceFlags,
        public readonly int $contentFlags,
    ) {
    }

    public static function fromBytes(string $bytes, int $offset, int $index): self
    {
        $raw = substr($bytes, $offset, self::MAX_QPATH);
        $nul = strpos($raw, "\0");
        $name = $nul === false ? $raw : substr($raw, 0, $nul);

        return new self(
            index: $index,
            shader: $name,
            surfaceFlags: BspBinary::int32($bytes, $offset + 64),
            contentFlags: BspBinary::int32($bytes, $offset + 68),
        );
    }
}
