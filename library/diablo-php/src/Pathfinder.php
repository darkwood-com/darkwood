<?php

declare(strict_types=1);

namespace Diablo;

/**
 * A* pathfinding (8 directions, diagonal cost, corner-cutting).
 * See NOTICE.md for attribution.
 */
final class Pathfinder
{
    public const AXIS_COST = 100;
    public const DIAG_COST = 101;
    public const MAX_PATH = 100;

    /** @var list<array{0: int, 1: int}> */
    private const DIRS = [
        [0, -1],  // N
        [1, -1],  // NE
        [1, 0],   // E
        [1, 1],   // SE
        [0, 1],   // S
        [-1, 1],  // SW
        [-1, 0],  // W
        [-1, -1], // NW
    ];

    /**
     * @param callable(int, int): bool|null $isBlocked extra occupancy check (monsters etc.)
     * @return list<array{0: int, 1: int}> path excluding start
     */
    public function find(
        Dungeon $dungeon,
        int $sx,
        int $sy,
        int $gx,
        int $gy,
        ?callable $isBlocked = null,
        bool $allowGoalBlocked = false,
    ): array {
        if ($sx === $gx && $sy === $gy) {
            return [];
        }

        $goalOk = $dungeon->isWalkable($gx, $gy)
            && ($allowGoalBlocked || $isBlocked === null || !$isBlocked($gx, $gy));
        if (!$goalOk && !$allowGoalBlocked) {
            return [];
        }

        $w = $dungeon->width;
        $key = static fn (int $x, int $y): int => $y * $w + $x;

        $open = [];
        $cameFrom = [];
        $gScore = [];
        $fScore = [];
        $closed = [];

        $start = $key($sx, $sy);
        $goal = $key($gx, $gy);
        $open[$start] = true;
        $gScore[$start] = 0;
        $fScore[$start] = $this->heuristic($sx, $sy, $gx, $gy);

        $nodes = 0;
        while ($open !== [] && $nodes < 2048) {
            $nodes++;
            $current = null;
            $bestF = PHP_INT_MAX;
            foreach ($open as $k => $_) {
                $f = $fScore[$k] ?? PHP_INT_MAX;
                if ($f < $bestF) {
                    $bestF = $f;
                    $current = $k;
                }
            }
            if ($current === null) {
                break;
            }
            if ($current === $goal) {
                $path = $this->reconstruct($cameFrom, $current, $w);
                if (count($path) > self::MAX_PATH) {
                    return array_slice($path, 0, self::MAX_PATH);
                }

                return $path;
            }

            unset($open[$current]);
            $closed[$current] = true;
            $cx = $current % $w;
            $cy = intdiv($current, $w);

            foreach (self::DIRS as [$dx, $dy]) {
                $nx = $cx + $dx;
                $ny = $cy + $dy;
                $nk = $key($nx, $ny);
                if (isset($closed[$nk])) {
                    continue;
                }

                $isGoal = ($nx === $gx && $ny === $gy);
                if (!$dungeon->isWalkable($nx, $ny) && !($allowGoalBlocked && $isGoal)) {
                    continue;
                }
                if ($isBlocked !== null && !$isGoal && $isBlocked($nx, $ny)) {
                    continue;
                }
                if (!$this->canStep($dungeon, $cx, $cy, $nx, $ny)) {
                    continue;
                }

                $stepCost = ($dx !== 0 && $dy !== 0) ? self::DIAG_COST : self::AXIS_COST;
                $tentative = ($gScore[$current] ?? PHP_INT_MAX) + $stepCost;
                if ($tentative >= ($gScore[$nk] ?? PHP_INT_MAX)) {
                    continue;
                }

                $cameFrom[$nk] = $current;
                $gScore[$nk] = $tentative;
                $fScore[$nk] = $tentative + $this->heuristic($nx, $ny, $gx, $gy);
                $open[$nk] = true;
            }
        }

        return [];
    }

    /**
     * Corner-cutting: for cardinal steps, both diagonal corners must be non-solid.
     * Uses GetPathDirection encoding for diagonal step checks.
     */
    public function canStep(Dungeon $dungeon, int $sx, int $sy, int $dx, int $dy): bool
    {
        $odx = $dx - $sx;
        $ody = $dy - $sy;
        // Diagonal moves: no extra corner check beyond destination walkability (DevX CanStep only checks cardinal).
        if ($odx !== 0 && $ody !== 0) {
            return true;
        }
        // Cardinal: check the two adjacent diagonal tiles at destination.
        // Direction relative to destination (as DevX does):
        // N (ody=-1): SW + SE of dest → (dx-1,dy+1), (dx+1,dy+1) relative to dest...
        // DevX: dest + SW and dest + SE for north, etc.
        if ($ody === -1 && $odx === 0) { // N
            return $dungeon->isNotSolid($dx - 1, $dy + 1) && $dungeon->isNotSolid($dx + 1, $dy + 1);
        }
        if ($odx === 1 && $ody === 0) { // E
            return $dungeon->isNotSolid($dx - 1, $dy + 1) && $dungeon->isNotSolid($dx - 1, $dy - 1);
        }
        if ($ody === 1 && $odx === 0) { // S
            return $dungeon->isNotSolid($dx + 1, $dy - 1) && $dungeon->isNotSolid($dx - 1, $dy - 1);
        }
        if ($odx === -1 && $ody === 0) { // W
            return $dungeon->isNotSolid($dx + 1, $dy + 1) && $dungeon->isNotSolid($dx + 1, $dy - 1);
        }

        return true;
    }

    private function heuristic(int $x, int $y, int $gx, int $gy): int
    {
        $adx = abs($x - $gx);
        $ady = abs($y - $gy);
        // Octile: diagonal + remaining axis, matching DevX cost scale roughly
        $diag = min($adx, $ady);
        $axis = abs($adx - $ady);

        return $diag * self::DIAG_COST + $axis * self::AXIS_COST;
    }

    /**
     * @param array<int, int> $cameFrom
     * @return list<array{0: int, 1: int}>
     */
    private function reconstruct(array $cameFrom, int $current, int $w): array
    {
        $path = [];
        while (isset($cameFrom[$current])) {
            $path[] = [$current % $w, intdiv($current, $w)];
            $current = $cameFrom[$current];
        }

        return array_reverse($path);
    }
}
