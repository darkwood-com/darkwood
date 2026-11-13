<?php

declare(strict_types=1);

namespace Diablo\Engine;

/**
 * A* pathfinding. See NOTICE.md for attribution.
 */
final class Path
{
    public const AXIS_COST = 100;
    public const DIAG_COST = 101;
    public const MAX_PATH = 25;

 /** Direction order matching path neighbor order where practical */
    private const DIRS = [
        [0, -1], [1, -1], [1, 0], [1, 1], [0, 1], [-1, 1], [-1, 0], [-1, -1],
    ];

    /**
     * @param callable(int,int):bool $isWalkable
     * @param callable(int,int,int,int):bool|null $canStep (fromX,fromY,toX,toY) corner cut
     * @return list<array{0:int,1:int}>
     */
    public function findPath(
        int $sx,
        int $sy,
        int $gx,
        int $gy,
        callable $isWalkable,
        ?callable $canStep = null,
        int $maxPath = self::MAX_PATH,
    ): array {
        if ($sx === $gx && $sy === $gy) {
            return [];
        }
        if (!$isWalkable($gx, $gy)) {
            return [];
        }

        $open = [[$sx, $sy, 0, $this->heuristic($sx, $sy, $gx, $gy), null]];
        $bestG = [$sx . ',' . $sy => 0];
        $came = [];
        $closed = [];

        while ($open !== []) {
            usort($open, static fn ($a, $b) => ($a[2] + $a[3]) <=> ($b[2] + $b[3]));
            $cur = array_shift($open);
            [$x, $y, $g] = $cur;
            $key = $x . ',' . $y;
            if (isset($closed[$key])) {
                continue;
            }
            $closed[$key] = true;
            if ($x === $gx && $y === $gy) {
                return $this->reconstruct($came, $gx, $gy, $sx, $sy, $maxPath);
            }
            foreach (self::DIRS as [$dx, $dy]) {
                $nx = $x + $dx;
                $ny = $y + $dy;
                $nkey = $nx . ',' . $ny;
                if (isset($closed[$nkey])) {
                    continue;
                }
                $diag = $dx !== 0 && $dy !== 0;
                if ($diag && $canStep !== null && !$canStep($x, $y, $nx, $ny)) {
                    continue;
                }
                if (!$isWalkable($nx, $ny) && !($nx === $gx && $ny === $gy)) {
                    continue;
                }
                $step = $diag ? self::DIAG_COST : self::AXIS_COST;
                $ng = $g + $step;
                if (isset($bestG[$nkey]) && $bestG[$nkey] <= $ng) {
                    continue;
                }
                $bestG[$nkey] = $ng;
                $came[$nkey] = [$x, $y];
                $open[] = [$nx, $ny, $ng, $this->heuristic($nx, $ny, $gx, $gy), $key];
            }
        }

        return [];
    }

    /**
     * Encode tile path as walkpath direction codes (terminated by WALK_NONE).
     * @param list<array{0:int,1:int}> $tiles successive tiles after start
     * @param int $sx start x
     * @param int $sy start y
     * @return list<int> dirs + WALK_NONE
     */
    public function tilesToWalkpath(int $sx, int $sy, array $tiles): array
    {
        $codes = [];
        $x = $sx;
        $y = $sy;
        foreach ($tiles as [$nx, $ny]) {
            $codes[] = Direction::fromDelta($nx - $x, $ny - $y);
            $x = $nx;
            $y = $ny;
        }
        $codes[] = DestAction::WALK_NONE;

        return $codes;
    }

    /**
     * @param callable(int,int):bool $isWalkable
     * @param callable(int,int,int,int):bool|null $canStep
     * @return list<int> walkpath direction codes ending with WALK_NONE
     */
    public function findWalkpath(
        int $sx,
        int $sy,
        int $gx,
        int $gy,
        callable $isWalkable,
        ?callable $canStep = null,
        bool $endspace = true,
        int $maxPath = self::MAX_PATH,
    ): array {
        $tiles = $this->findPath($sx, $sy, $gx, $gy, $isWalkable, $canStep, $maxPath);
        if ($tiles === []) {
            return [DestAction::WALK_NONE];
        }
        // MakePlrPath endspace=false: drop last step (stop adjacent)
        if (!$endspace && count($tiles) > 0) {
            array_pop($tiles);
            if ($tiles === []) {
                return [DestAction::WALK_NONE];
            }
        }

        return $this->tilesToWalkpath($sx, $sy, $tiles);
    }

    private function heuristic(int $x, int $y, int $gx, int $gy): int
    {
        $adx = abs($x - $gx);
        $ady = abs($y - $gy);
        $diag = min($adx, $ady);
        $axis = abs($adx - $ady);

        return $diag * self::DIAG_COST + $axis * self::AXIS_COST;
    }

    /**
     * @param array<string, array{0:int,1:int}> $came
     * @return list<array{0:int,1:int}>
     */
    private function reconstruct(array $came, int $gx, int $gy, int $sx, int $sy, int $maxPath): array
    {
        $path = [];
        $x = $gx;
        $y = $gy;
        while (!($x === $sx && $y === $sy)) {
            array_unshift($path, [$x, $y]);
            $key = $x . ',' . $y;
            if (!isset($came[$key])) {
                return [];
            }
            [$x, $y] = $came[$key];
        }

        // rejects paths > MaxPathLength; for playability walk the first
        // MaxPathLength steps toward the goal so distant clicks still make progress.
        if (count($path) > $maxPath) {
            return array_slice($path, 0, $maxPath);
        }

        return $path;
    }
}
