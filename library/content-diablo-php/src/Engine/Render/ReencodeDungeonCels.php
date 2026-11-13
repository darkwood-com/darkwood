<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

/**
 * Re-encodes dungeon CELs (remove triangle/trapezoid padding; extract floor foliage).
 * See NOTICE.md for attribution.
 */
final class ReencodeDungeonCels
{
    public const DUN_FRAME_WIDTH = 32;
    public const DUN_FRAME_HEIGHT = 32;
    public const REENCODED_TRIANGLE_FRAME_SIZE = 512; // 544 - 32
    public const REENCODED_TRAPEZOID_FRAME_SIZE = 784; // 800 - 16

    public const TYPE_SQUARE = 0;
    public const TYPE_TRANSPARENT_SQUARE = 1;
    public const TYPE_LEFT_TRIANGLE = 2;
    public const TYPE_RIGHT_TRIANGLE = 3;
    public const TYPE_LEFT_TRAPEZOID = 4;
    public const TYPE_RIGHT_TRAPEZOID = 5;

    /** TileProperties::Solid */
    private const PROP_SOLID = 1 << 0;
    /** TileProperties::BlockMissile */
    private const PROP_BLOCK_MISSILE = 1 << 2;

    /**
     * @param list<array{0:int,1:array{microTileIndex:int,type:int,properties:int}}> $frames
     *        sorted by frame index (1-based CEL frame numbers as in )
     * @return array{cel:string, adjustments:list<array{0:int,1:int}>}
     */
    public static function reencode(string $dungeonCels, array $frames): array
    {
        $numFrames = count($frames);
        $headerSize = (2 + $numFrames) * 4;
        $frameBlobs = [];

        foreach ($frames as [$frame, $info]) {
            $srcFrameBegin = self::readU32($dungeonCels, $frame * 4);
            $srcPos = $srcFrameBegin;
            $type = (int) $info['type'];

            switch ($type) {
                case self::TYPE_TRANSPARENT_SQUARE:
                    if (self::isFloor($info)) {
                        $newType = self::isFloorLeft($info)
                            ? self::TYPE_LEFT_TRIANGLE
                            : self::TYPE_RIGHT_TRIANGLE;
                        $frameBlobs[] = self::reencodeFloorWithFoliage($dungeonCels, $srcPos, $newType);
                    } else {
                        $srcFrameEnd = self::readU32($dungeonCels, ($frame + 1) * 4);
                        $frameBlobs[] = substr($dungeonCels, $srcFrameBegin, $srcFrameEnd - $srcFrameBegin);
                    }
                    break;

                case self::TYPE_SQUARE:
                    $frameBlobs[] = substr($dungeonCels, $srcFrameBegin, self::DUN_FRAME_WIDTH * self::DUN_FRAME_HEIGHT);
                    break;

                case self::TYPE_LEFT_TRIANGLE:
                    $frameBlobs[] = self::reencodeLeftTriangle($dungeonCels, $srcPos);
                    break;

                case self::TYPE_RIGHT_TRIANGLE:
                    $frameBlobs[] = self::reencodeRightTriangle($dungeonCels, $srcPos);
                    break;

                case self::TYPE_LEFT_TRAPEZOID:
                    $frameBlobs[] = self::reencodeLeftTrapezoid($dungeonCels, $srcPos);
                    break;

                case self::TYPE_RIGHT_TRAPEZOID:
                    $frameBlobs[] = self::reencodeRightTrapezoid($dungeonCels, $srcPos);
                    break;

                default:
                    $frameBlobs[] = '';
                    break;
            }
        }

        $outSize = $headerSize;
        foreach ($frameBlobs as $blob) {
            $outSize += strlen($blob);
        }

        $cel = pack('V', $numFrames);
        $offset = $headerSize;
        foreach ($frameBlobs as $blob) {
            $cel .= pack('V', $offset);
            $offset += strlen($blob);
        }
        $cel .= pack('V', $outSize);
        foreach ($frameBlobs as $blob) {
            $cel .= $blob;
        }

        return [
            'cel' => $cel,
            'adjustments' => self::computeCelBlockAdjustments($frames),
        ];
    }

    /**
     * @param array{microTileIndex:int,type:int,properties:int} $info
     */
    private static function isFloor(array $info): bool
    {
        // DunFrameInfo::isFloor — BlockMissile excludes L3/L4 stairs.
        $props = (int) $info['properties'];
        $micro = (int) $info['microTileIndex'];

        return ($props & (self::PROP_SOLID | self::PROP_BLOCK_MISSILE)) === 0
            && ($micro === 0 || $micro === 1);
    }

    /**
     * @param array{microTileIndex:int,type:int,properties:int} $info
     */
    private static function isFloorLeft(array $info): bool
    {
        return (int) $info['microTileIndex'] === 0;
    }

    /**
     * @param list<array{0:int,1:array{microTileIndex:int,type:int,properties:int}}> $frames
     * @return list<array{0:int,1:int}>
     */
    private static function computeCelBlockAdjustments(array $frames): array
    {
        $celBlockAdjustments = [];
        $lastFrameIndex = 0;
        $adjustment = 0;
        foreach ($frames as [$frame]) {
            $frame = (int) $frame;
            $diff = $frame - $lastFrameIndex - 1;
            if ($diff > 0) {
                $celBlockAdjustments[] = [$frame, $adjustment];
            }
            $adjustment += $diff;
            $lastFrameIndex = $frame;
        }
        if ($adjustment > 0) {
            $celBlockAdjustments[] = [0xFFFF, $adjustment];
        }

        return $celBlockAdjustments;
    }

    private static function readU32(string $data, int $byteOffset): int
    {
        return unpack('V', substr($data, $byteOffset, 4))[1];
    }

    private static function reencodeLeftTriangleLower(string $src, int &$srcPos): string
    {
        $dst = '';
        $width = 0;
        for ($i = 0; $i < 8; ++$i) {
            $srcPos += 2;
            $width += 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
            $width += 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
        }

        return $dst;
    }

    private static function reencodeLeftTriangle(string $src, int $srcPos): string
    {
        $dst = self::reencodeLeftTriangleLower($src, $srcPos);
        $width = self::DUN_FRAME_WIDTH;
        for ($i = 0; $i < 7; ++$i) {
            $srcPos += 2;
            $width -= 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
            $width -= 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
        }
        $srcPos += 2;
        $width -= 2;
        $dst .= substr($src, $srcPos, $width);

        return $dst;
    }

    private static function reencodeRightTriangleLower(string $src, int &$srcPos): string
    {
        $dst = '';
        $width = 0;
        for ($i = 0; $i < 8; ++$i) {
            $width += 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width + 2;
            $width += 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
        }

        return $dst;
    }

    private static function reencodeRightTriangle(string $src, int $srcPos): string
    {
        $dst = self::reencodeRightTriangleLower($src, $srcPos);
        $width = self::DUN_FRAME_WIDTH;
        for ($i = 0; $i < 7; ++$i) {
            $width -= 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width + 2;
            $width -= 2;
            $dst .= substr($src, $srcPos, $width);
            $srcPos += $width;
        }
        $width -= 2;
        $dst .= substr($src, $srcPos, $width);

        return $dst;
    }

    private static function reencodeLeftTrapezoid(string $src, int $srcPos): string
    {
        $dst = self::reencodeLeftTriangleLower($src, $srcPos);
        $dst .= substr($src, $srcPos, self::DUN_FRAME_WIDTH * 16);

        return $dst;
    }

    private static function reencodeRightTrapezoid(string $src, int $srcPos): string
    {
        $dst = self::reencodeRightTriangleLower($src, $srcPos);
        $dst .= substr($src, $srcPos, self::DUN_FRAME_WIDTH * 16);

        return $dst;
    }

    /**
     * Decode TransparentSquare RLE into a full 32×32 surface (bottom-up into a top-down buffer).
     * Mutates $surface in place. $dstOffset is the start of the last row.
     */
    private static function renderTransparentSquare(string &$surface, int $dstOffset, string $src, int $srcPos): void
    {
        $w = self::DUN_FRAME_WIDTH;
        $h = self::DUN_FRAME_HEIGHT;
        for ($i = 0; $i < $h; ++$i) {
            $drawWidth = $w;
            $rowPos = $dstOffset;
            while ($drawWidth > 0) {
                $v = unpack('c', $src[$srcPos++])[1];
                if ($v > 0) {
                    for ($j = 0; $j < $v; ++$j) {
                        $surface[$rowPos + $j] = $src[$srcPos + $j];
                    }
                    $srcPos += $v;
                } else {
                    $v = -$v;
                }
                $rowPos += $v;
                $drawWidth -= $v;
            }
            // C++ advances dst by one row during the scan, then subtracts 2*width
            // (net -1 row). We keep dstOffset at the row start, so step back one row.
            $dstOffset -= $w;
        }
    }

    private static function extractFoliageLeftTriangle(string &$surface, int $srcOffset): string
    {
        $w = self::DUN_FRAME_WIDTH;
        $dst = '';
        for ($width = 2, $y = 31; $y >= 16; --$y, $width += 2, $srcOffset -= $w) {
            $off = $srcOffset + ($w - $width);
            $dst .= substr($surface, $off, $width);
            for ($j = 0; $j < $width; ++$j) {
                $surface[$off + $j] = "\0";
            }
        }
        for ($width = 30, $y = 15; $y > 0; --$y, $width -= 2, $srcOffset -= $w) {
            $off = $srcOffset + ($w - $width);
            $dst .= substr($surface, $off, $width);
            for ($j = 0; $j < $width; ++$j) {
                $surface[$off + $j] = "\0";
            }
        }

        return $dst;
    }

    private static function extractFoliageRightTriangle(string &$surface, int $srcOffset): string
    {
        $w = self::DUN_FRAME_WIDTH;
        $dst = '';
        for ($width = 2, $y = 31; $y >= 16; --$y, $width += 2, $srcOffset -= $w) {
            $dst .= substr($surface, $srcOffset, $width);
            for ($j = 0; $j < $width; ++$j) {
                $surface[$srcOffset + $j] = "\0";
            }
        }
        for ($width = 30, $y = 15; $y > 0; --$y, $width -= 2, $srcOffset -= $w) {
            $dst .= substr($surface, $srcOffset, $width);
            for ($j = 0; $j < $width; ++$j) {
                $surface[$srcOffset + $j] = "\0";
            }
        }

        return $dst;
    }

    private static function extractFoliageTransparentSquare(string &$surface, int $srcOffset): string
    {
        // Bottom 16 lines are always transparent; foliage only in the upper half.
        $srcOffset -= self::DUN_FRAME_HEIGHT * 16;
        $w = self::DUN_FRAME_WIDTH;
        $dst = '';
        for ($y = 16; $y > 0; --$y) {
            $transparentRun = 0;
            $solidRun = 0;
            $rowStart = $srcOffset;
            for ($x = 0; $x < $w; ++$x) {
                $pix = $surface[$srcOffset++];
                if ($pix !== "\0") {
                    if ($transparentRun !== 0) {
                        $dst .= pack('c', -$transparentRun);
                        $transparentRun = 0;
                    }
                    ++$solidRun;
                } else {
                    if ($solidRun !== 0) {
                        $dst .= chr($solidRun) . substr($surface, $srcOffset - $solidRun, $solidRun);
                        $solidRun = 0;
                    }
                    ++$transparentRun;
                }
            }
            if ($transparentRun !== 0) {
                $dst .= pack('c', -$transparentRun);
            } elseif ($solidRun !== 0) {
                $dst .= chr($solidRun) . substr($surface, $srcOffset - $solidRun, $solidRun);
            }
            // After walking the row, src is at next row; step back two rows (same as C++).
            $srcOffset = $rowStart - 2 * $w;
        }

        return $dst;
    }

    private static function reencodeFloorWithFoliage(string $src, int $srcPos, int $tileType): string
    {
        $surface = str_repeat("\0", self::DUN_FRAME_WIDTH * self::DUN_FRAME_HEIGHT);
        $surfaceLastLine = self::DUN_FRAME_WIDTH * (self::DUN_FRAME_HEIGHT - 1);
        self::renderTransparentSquare($surface, $surfaceLastLine, $src, $srcPos);

        if ($tileType === self::TYPE_LEFT_TRIANGLE) {
            $dst = self::extractFoliageLeftTriangle($surface, $surfaceLastLine);
        } else {
            $dst = self::extractFoliageRightTriangle($surface, $surfaceLastLine);
        }
        $dst .= self::extractFoliageTransparentSquare($surface, $surfaceLastLine);

        return $dst;
    }
}
