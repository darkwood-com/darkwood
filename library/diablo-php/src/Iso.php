<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Isometric grid ↔ screen transforms. Math lives only here.
 */
final class Iso
{
    public const TILE_W = 64;
    public const TILE_H = 32;

    public function __construct(
        public int $originX,
        public int $originY,
    ) {
    }

    /**
     * @return array{0: int, 1: int} screen pixel of tile center
     */
    public function gridToScreen(int $gx, int $gy): array
    {
        $sx = (int) (($gx - $gy) * (self::TILE_W / 2) + $this->originX);
        $sy = (int) (($gx + $gy) * (self::TILE_H / 2) + $this->originY);

        return [$sx, $sy];
    }

    /**
     * Inverse of gridToScreen for mouse picking (tile under cursor).
     *
     * @return array{0: int, 1: int}
     */
    public function screenToGrid(int $sx, int $sy): array
    {
        $rx = $sx - $this->originX;
        $ry = $sy - $this->originY;

        // Solve:
        // rx = (gx - gy) * (TW/2)
        // ry = (gx + gy) * (TH/2)
        $gx = (int) floor(($rx / (self::TILE_W / 2) + $ry / (self::TILE_H / 2)) / 2);
        $gy = (int) floor(($ry / (self::TILE_H / 2) - $rx / (self::TILE_W / 2)) / 2);

        return [$gx, $gy];
    }
}
