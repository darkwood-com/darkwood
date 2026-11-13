<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * 8-way direction vectors.
 * World deltas are isometric (not Cartesian axis steps).
 */
final class Direction
{
    public const S = 0;
    public const SW = 1;
    public const W = 2;
    public const NW = 3;
    public const N = 4;
    public const NE = 5;
    public const E = 6;
    public const SE = 7;

    /** @return array{0:int,1:int} */
    public static function delta(int $dir): array
    {
        // DisplacementOf::fromDirection
        return match ($dir) {
            self::S => [1, 1],
            self::SW => [0, 1],
            self::W => [-1, 1],
            self::NW => [-1, 0],
            self::N => [-1, -1],
            self::NE => [0, -1],
            self::E => [1, -1],
            self::SE => [1, 0],
            default => [0, 0],
        };
    }

    public static function fromDelta(int $dx, int $dy): int
    {
        // Exact neighbor match first (GetDirection for adjacent)
        return match (true) {
            $dx === 1 && $dy === 1 => self::S,
            $dx === 0 && $dy === 1 => self::SW,
            $dx === -1 && $dy === 1 => self::W,
            $dx === -1 && $dy === 0 => self::NW,
            $dx === -1 && $dy === -1 => self::N,
            $dx === 0 && $dy === -1 => self::NE,
            $dx === 1 && $dy === -1 => self::E,
            $dx === 1 && $dy === 0 => self::SE,
            // Fallback for non-unit vectors (sign pattern)
            $dx > 0 && $dy > 0 => self::S,
            $dx === 0 && $dy > 0 => self::SW,
            $dx < 0 && $dy > 0 => self::W,
            $dx < 0 && $dy === 0 => self::NW,
            $dx < 0 && $dy < 0 => self::N,
            $dx === 0 && $dy < 0 => self::NE,
            $dx > 0 && $dy < 0 => self::E,
            $dx > 0 && $dy === 0 => self::SE,
            default => self::S,
        };
    }

    /** Full-tile sprite MovingOffset for this direction. */
    /** @return array{0:int,1:int} */
    public static function movingOffset(int $dir): array
    {
        return match ($dir) {
            self::S => [0, 32],
            self::SW => [-32, 16],
            self::W => [-64, 0],
            self::NW => [-32, -16],
            self::N => [0, -32],
            self::NE => [32, -16],
            self::E => [64, 0],
            self::SE => [32, 16],
            default => [0, 0],
        };
    }
}
