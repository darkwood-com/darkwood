<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * Little-endian reads matching Q3 LittleLong / LittleFloat on LE hosts.
 */
final class BspBinary
{
    public static function int32(string $bytes, int $offset): int
    {
        $v = unpack('V', substr($bytes, $offset, 4));
        if ($v === false) {
            throw new \RuntimeException(sprintf('unpack int32 at %d failed', $offset));
        }
        // LittleLong is signed; unpack('V') is unsigned.
        $u = $v[1];
        return $u >= 0x80000000 ? $u - 0x100000000 : $u;
    }

    public static function float32(string $bytes, int $offset): float
    {
        $v = unpack('g', substr($bytes, $offset, 4));
        if ($v === false) {
            throw new \RuntimeException(sprintf('unpack float32 at %d failed', $offset));
        }
        return $v[1];
    }

    /**
     * @return list<int>
     */
    public static function int32Array(string $bytes, int $offset, int $count): array
    {
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[] = self::int32($bytes, $offset + $i * 4);
        }
        return $out;
    }
}
