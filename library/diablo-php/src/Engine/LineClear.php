<?php

declare(strict_types=1);

namespace Diablo\Engine;

use Diablo\Levels\DungeonMap;

/**
 * LineClear / LineClearMissile (monster.cpp) — Bresenham LOS.
 */
final class LineClear
{
    /**
     * @param callable(int,int):bool $clear PosOk — false stops the ray
     */
    public static function lineClear(callable $clear, int $x0, int $y0, int $x1, int $y1): bool
    {
        $px = $x0;
        $py = $y0;
        $ex = $x1;
        $ey = $y1;
        $dx = $ex - $px;
        $dy = $ey - $py;
        if (abs($dx) > abs($dy)) {
            if ($dx < 0) {
                [$px, $py, $ex, $ey] = [$ex, $ey, $px, $py];
                $dx = -$dx;
                $dy = -$dy;
            }
            if ($dy > 0) {
                $d = 2 * $dy - $dx;
                $dincD = 2 * $dy;
                $dincH = 2 * ($dy - $dx);
                $yincD = 1;
            } else {
                $d = 2 * $dy + $dx;
                $dincD = 2 * $dy;
                $dincH = 2 * ($dx + $dy);
                $yincD = -1;
            }
            $done = false;
            while (!$done && !($px === $ex && $py === $ey)) {
                if (($d <= 0) xor ($yincD < 0)) {
                    $d += $dincD;
                } else {
                    $d += $dincH;
                    $py += $yincD;
                }
                $px++;
                $done = !($px === $x0 && $py === $y0) && !$clear($px, $py);
            }
        } else {
            if ($dy < 0) {
                [$px, $py, $ex, $ey] = [$ex, $ey, $px, $py];
                $dy = -$dy;
                $dx = -$dx;
            }
            if ($dx > 0) {
                $d = 2 * $dx - $dy;
                $dincD = 2 * $dx;
                $dincH = 2 * ($dx - $dy);
                $xincD = 1;
            } else {
                $d = 2 * $dx + $dy;
                $dincD = 2 * $dx;
                $dincH = 2 * ($dy + $dx);
                $xincD = -1;
            }
            $done = false;
            while (!$done && !($px === $ex && $py === $ey)) {
                if (($d <= 0) xor ($xincD < 0)) {
                    $d += $dincD;
                } else {
                    $d += $dincH;
                    $px += $xincD;
                }
                $py++;
                $done = !($px === $x0 && $py === $y0) && !$clear($px, $py);
            }
        }

        return $px === $ex && $py === $ey;
    }

    /** LineClearMissile — PosOkMissile = !IsMissileBlockedByTile (optional object-aware clear). */
    public static function missile(DungeonMap $map, int $x0, int $y0, int $x1, int $y1, ?callable $posOkMissile = null): bool
    {
        $posOkMissile ??= static fn (int $x, int $y): bool => !$map->tileHas($x, $y, DungeonMap::SOL_BLOCK_MISSILE);

        return self::lineClear($posOkMissile, $x0, $y0, $x1, $y1);
    }

    /** IsLineNotSolid — !TileProperties::Solid. */
    public static function notSolid(DungeonMap $map, int $x0, int $y0, int $x1, int $y1): bool
    {
        return self::lineClear(
            static fn (int $x, int $y): bool => !$map->isSolid($x, $y),
            $x0,
            $y0,
            $x1,
            $y1,
        );
    }
}
