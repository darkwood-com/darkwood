<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

use Diablo\Engine\Assets\Palette;

/**
 * Dungeon micro (level CEL) decode to RGBA.
 * Dungeon microtile CEL decode (see NOTICE.md).
 * After ReencodeDungeonCels: triangles are pad-stripped (512 bytes); floor frames are
 * triangle pixels + TransparentSquare foliage RLE (DrawFloorTile + RenderTileFoliage).
 */
final class DunMicro
{
    public const TYPE_SQUARE = 0;
    public const TYPE_TRANSPARENT_SQUARE = 1;
    public const TYPE_LEFT_TRIANGLE = 2;
    public const TYPE_RIGHT_TRIANGLE = 3;
    public const TYPE_LEFT_TRAPEZOID = 4;
    public const TYPE_RIGHT_TRAPEZOID = 5;

    public const W = 32;
    public const H = 32;
    public const TRIANGLE_HEIGHT = 31;
    public const REENCODED_TRIANGLE_SIZE = 512;
    /** Lower triangle only (2+4+…+32) — trapezoid prefix after reencode. */
    public const REENCODED_TRIANGLE_LOWER_SIZE = 272;
    public const FOLIAGE_HEIGHT = 16;

    /**
     * @param string $mode ''|floor_triangle|foliage
     * @param list<int>|null $lightTable LightTables row (palette index remap); null = fullbright
     * @return array{w:int,h:int,rgba:string}
     */
    public static function decode(string $src, int $type, Palette $pal, bool $reencoded = false, string $mode = '', ?array $lightTable = null): array
    {
        if ($mode === 'foliage') {
            return self::decodeFoliage($src, $pal, $lightTable);
        }

        $w = self::W;
        $h = self::H;
        $buf = array_fill(0, $w * $h, null); // palette index or null
        $pos = 0;
        $len = strlen($src);
        // Floor triangles only use first 512 bytes after reencode.
        if ($reencoded && ($type === self::TYPE_LEFT_TRIANGLE || $type === self::TYPE_RIGHT_TRIANGLE)) {
            $len = min($len, self::REENCODED_TRIANGLE_SIZE);
            $src = substr($src, 0, $len);
        }

        try {
            match ($type) {
                self::TYPE_SQUARE => self::decodeSquare($src, $buf, $pos),
                self::TYPE_TRANSPARENT_SQUARE => self::decodeTransparentSquare($src, $buf, $pos, $len),
                self::TYPE_LEFT_TRIANGLE => $reencoded
                    ? self::decodeLeftTriangleReencoded($src, $buf, $pos, $len)
                    : self::decodeLeftTriangle($src, $buf, $pos, $len),
                self::TYPE_RIGHT_TRIANGLE => $reencoded
                    ? self::decodeRightTriangleReencoded($src, $buf, $pos, $len)
                    : self::decodeRightTriangle($src, $buf, $pos, $len),
                self::TYPE_LEFT_TRAPEZOID => $reencoded
                    ? self::decodeLeftTrapezoidReencoded($src, $buf, $pos, $len)
                    : self::decodeLeftTrapezoid($src, $buf, $pos, $len),
                self::TYPE_RIGHT_TRAPEZOID => $reencoded
                    ? self::decodeRightTrapezoidReencoded($src, $buf, $pos, $len)
                    : self::decodeRightTrapezoid($src, $buf, $pos, $len),
                default => throw new \InvalidArgumentException('unknown micro type ' . $type),
            };
        } catch (\Throwable $e) {
            throw new \RuntimeException('DunMicro decode failed type=' . $type . ': ' . $e->getMessage(), 0, $e);
        }

        return ['w' => $w, 'h' => $h, 'rgba' => self::indicesToRgba($buf, $w * $h, $pal, $lightTable)];
    }

    /** @param list<?int> $buf */
    private static function indicesToRgba(array $buf, int $n, Palette $pal, ?array $lightTable): string
    {
        // RGBA dungeon path: undecoded cells (null) and palette index 0 are transparent.
        //
        // DX's 8-bit Solid blit memcpy's index 0 into the indexed buffer (displays as
        // palette black). In our RGBA/SDL pipeline that overwrites correctly drawn floor
        // with opaque RGB(0,0,0) and creates Town "black diamond" holes under tree/wall
        // Square and TransparentSquare micros (frames that use 0 as empty/shadow texels).
        // Index 127 remains a normal opaque level color (may also be black in town.pal).
        $rgba = '';
        for ($i = 0; $i < $n; $i++) {
            $idx = $buf[$i];
            if ($idx === null || $idx === 0) {
                $rgba .= "\x00\x00\x00\x00";
                continue;
            }
            $c = (int) $idx;
            if ($lightTable !== null) {
                $c = (int) ($lightTable[$c] ?? $c);
                if ($c === 0) {
                    $rgba .= "\x00\x00\x00\x00";
                    continue;
                }
            }
            [$r, $g, $b] = $pal->rgb($c);
            $rgba .= chr($r) . chr($g) . chr($b) . "\xFF";
        }

        return $rgba;
    }

    /** GetDunFrameFoliage + RenderTileFoliage (height 16 TransparentSquare). */
    public static function decodeFoliage(string $src, Palette $pal, ?array $lightTable = null): array
    {
        $tri = self::REENCODED_TRIANGLE_SIZE;
        if (strlen($src) <= $tri) {
            $empty = str_repeat("\x00\x00\x00\x00", self::W * self::FOLIAGE_HEIGHT);

            return ['w' => self::W, 'h' => self::FOLIAGE_HEIGHT, 'rgba' => $empty];
        }
        $foliage = substr($src, $tri);
        $w = self::W;
        $h = self::FOLIAGE_HEIGHT;
        $buf = array_fill(0, $w * $h, null);
        $pos = 0;
        $len = strlen($foliage);
        for ($y = $h - 1; $y >= 0; $y--) {
            $x = 0;
            while ($x < $w && $pos < $len) {
                $v = unpack('c', $foliage[$pos++])[1];
                if ($v > 0) {
                    for ($i = 0; $i < $v && $x < $w && $pos < $len; $i++, $x++) {
                        $buf[$y * $w + $x] = ord($foliage[$pos++]);
                    }
                } else {
                    $x += -$v;
                }
            }
        }

        return ['w' => $w, 'h' => $h, 'rgba' => self::indicesToRgba($buf, $w * $h, $pal, $lightTable)];
    }

    /** @param list<?int> $buf */
    private static function decodeSquare(string $src, array &$buf, int &$pos): void
    {
        // Level CEL Square: bottom row first (RenderSquareFull).
        $len = strlen($src);
        for ($i = 0; $i < 32; $i++) {
            $y = 31 - $i;
            for ($x = 0; $x < 32; $x++) {
                if ($pos >= $len) {
                    return;
                }
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
        }
    }

    /** @param list<?int> $buf */
    private static function decodeTransparentSquare(string $src, array &$buf, int &$pos, int $len): void
    {
        for ($y = 31; $y >= 0; $y--) {
            $x = 0;
            while ($x < 32 && $pos < $len) {
                $v = unpack('c', $src[$pos++])[1];
                if ($v > 0) {
                    for ($i = 0; $i < $v && $x < 32 && $pos < $len; $i++, $x++) {
                        $buf[$y * 32 + $x] = ord($src[$pos++]);
                    }
                } else {
                    $x += -$v;
                }
            }
        }
    }

    /** @param list<?int> $buf */
    private static function decodeLeftTriangle(string $src, array &$buf, int &$pos, int $len): void
    {
        // Bottom-up 31 rows; widths 2,4,...32,...2 with 2 pad bytes before even rows
        for ($i = 0; $i < 31; $i++) {
            if (($i & 1) === 0) {
                $pos += 2; // padding
            }
            $width = $i < 16 ? ($i + 1) * 2 : (31 - $i) * 2;
            $x0 = 32 - $width;
            $y = 30 - $i;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
        }
    }

    /** @param list<?int> $buf */
    private static function decodeRightTriangle(string $src, array &$buf, int &$pos, int $len): void
    {
        for ($i = 0; $i < 31; $i++) {
            $width = $i < 16 ? ($i + 1) * 2 : (31 - $i) * 2;
            $y = 30 - $i;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
            if (($i & 1) === 0) {
                $pos += 2;
            }
        }
    }

    /** @param list<?int> $buf */
    private static function decodeLeftTrapezoid(string $src, array &$buf, int &$pos, int $len): void
    {
        // Lower triangle only (16 rows), then 16×32 rectangle (bottom-up into y=15..0).
        self::decodeLeftTriangleLower($src, $buf, $pos, $len);
        self::decodeTrapezoidUpperRect($src, $buf, $pos, $len);
    }

    /** @param list<?int> $buf */
    private static function decodeRightTrapezoid(string $src, array &$buf, int &$pos, int $len): void
    {
        self::decodeRightTriangleLower($src, $buf, $pos, $len);
        self::decodeTrapezoidUpperRect($src, $buf, $pos, $len);
    }

    /** Left triangle lower half only (i=0..15 → y=30..15). */
    private static function decodeLeftTriangleLower(string $src, array &$buf, int &$pos, int $len): void
    {
        for ($i = 0; $i < 16; $i++) {
            if (($i & 1) === 0) {
                $pos += 2;
            }
            $width = ($i + 1) * 2;
            $x0 = 32 - $width;
            $y = 30 - $i;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
        }
    }

    /** Right triangle lower half only. */
    private static function decodeRightTriangleLower(string $src, array &$buf, int &$pos, int $len): void
    {
        for ($i = 0; $i < 16; $i++) {
            $width = ($i + 1) * 2;
            $y = 30 - $i;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
            if (($i & 1) === 0) {
                $pos += 2;
            }
        }
    }

    /** Trapezoid upper 16×32: CEL bottom-up → buffer y=15 first down to y=0. */
    private static function decodeTrapezoidUpperRect(string $src, array &$buf, int &$pos, int $len): void
    {
        for ($i = 0; $i < 16; $i++) {
            $y = 15 - $i;
            for ($x = 0; $x < 32 && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
        }
    }

 /** Reencoded left triangle: no pad bytes (after ReencodeDungeonCels). */
    private static function decodeLeftTriangleReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        $width = 0;
        for ($i = 0; $i < 8; $i++) {
            $width += 2;
            $y = 30 - ($i * 2);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
            $width += 2;
            $y = 30 - ($i * 2 + 1);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
        }
        $width = 32;
        for ($i = 0; $i < 7; $i++) {
            $width -= 2;
            $y = 14 - ($i * 2);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
            $width -= 2;
            $y = 14 - ($i * 2 + 1);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
        }
        $width -= 2;
        $y = 0;
        $x0 = 32 - $width;
        for ($x = 0; $x < $width && $pos < $len; $x++) {
            $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
        }
    }

    private static function decodeRightTriangleReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        $width = 0;
        for ($i = 0; $i < 8; $i++) {
            $width += 2;
            $y = 30 - ($i * 2);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
            $width += 2;
            $y = 30 - ($i * 2 + 1);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
        }
        $width = 32;
        for ($i = 0; $i < 7; $i++) {
            $width -= 2;
            $y = 14 - ($i * 2);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
            $width -= 2;
            $y = 14 - ($i * 2 + 1);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
        }
        $width -= 2;
        for ($x = 0; $x < $width && $pos < $len; $x++) {
            $buf[$x] = ord($src[$pos++]);
        }
    }

    /** Reencoded trapezoid: lower triangle (272) + upper rect (512). */
    private static function decodeLeftTrapezoidReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        self::decodeLeftTriangleLowerReencoded($src, $buf, $pos, $len);
        self::decodeTrapezoidUpperRect($src, $buf, $pos, $len);
    }

    private static function decodeRightTrapezoidReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        self::decodeRightTriangleLowerReencoded($src, $buf, $pos, $len);
        self::decodeTrapezoidUpperRect($src, $buf, $pos, $len);
    }

    private static function decodeLeftTriangleLowerReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        $width = 0;
        for ($i = 0; $i < 8; $i++) {
            $width += 2;
            $y = 30 - ($i * 2);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
            $width += 2;
            $y = 30 - ($i * 2 + 1);
            $x0 = 32 - $width;
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x0 + $x] = ord($src[$pos++]);
            }
        }
    }

    private static function decodeRightTriangleLowerReencoded(string $src, array &$buf, int &$pos, int $len): void
    {
        $width = 0;
        for ($i = 0; $i < 8; $i++) {
            $width += 2;
            $y = 30 - ($i * 2);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
            $width += 2;
            $y = 30 - ($i * 2 + 1);
            for ($x = 0; $x < $width && $pos < $len; $x++) {
                $buf[$y * 32 + $x] = ord($src[$pos++]);
            }
        }
    }
}
