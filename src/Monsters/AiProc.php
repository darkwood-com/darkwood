<?php

declare(strict_types=1);

namespace Diablo\Monsters;

use Diablo\Engine\Direction;
use Diablo\Engine\LineClear;
use Diablo\Engine\Path;
use Diablo\Engine\Random;
use Diablo\Levels\DungeonMap;

/**
 * AiProc dispatch for cathedral-encountered AIs (Zombie, Skeleton, Goat*, Fallen, Scavenger, …).
 * AI only runs when mode==Stand; WalkInDirection sets future without moving tile.
 */
final class AiProc
{
    /** Monster walk frames (WalkInDirection → MonsterWalk last-frame commit). */
    public const WALK_FRAMES = 8;

    /**
     * @param array<string,mixed> $m
     * @param list<array<string,mixed>> $monsters
     * @param callable(int,int):bool $posOk IsTileAccessible
     * @param callable(int,int):bool|null $missOk PosOkMissile (!IsMissileBlockedByTile)
     * @return array<string,mixed>
     */
    public static function tick(array $m, int $px, int $py, DungeonMap $map, Random $rng, array $monsters, ?callable $posOk = null, ?callable $missOk = null): array
    {
        if (empty($m['alive'])) {
            $m['mode'] = 'DEATH';

            return $m;
        }
        // : AI gated on Stand (isWalking skips AI)
        if (($m['mode'] ?? '') === 'WALK') {
            return $m;
        }
        $posOk ??= static fn (int $x, int $y): bool => $map->isWalkable($x, $y);
        $missOk ??= static fn (int $x, int $y): bool => !$map->tileHas($x, $y, DungeonMap::SOL_BLOCK_MISSILE);
        $ai = (string) ($m['ai'] ?? 'Zombie');
        $dist = max(abs($m['x'] - $px), abs($m['y'] - $py));
        // Same dTransVal room or clear solid LOS (MAI_* activation gate)
        if ($dist > 1 && !self::canSensePlayer($map, $m, $px, $py)) {
            $m['mode'] = 'STAND';

            return $m;
        }

        return match ($ai) {
            'Skeleton', 'SkeletonBow', 'BoneDemon' => self::skeletonAi($m, $px, $py, $map, $rng, $dist, $posOk, $missOk),
            'GoatMc', 'GoatBow', 'GoatLord' => self::goatAi($m, $px, $py, $map, $rng, $dist, $posOk, $missOk),
            'Fallen' => self::fallenAi($m, $px, $py, $map, $rng, $dist, $posOk),
            'Scavenger' => self::scavengerAi($m, $px, $py, $map, $rng, $dist, $posOk),
            default => self::zombieAi($m, $px, $py, $map, $rng, $dist, $posOk),
        };
    }

    /** @param array<string,mixed> $m */
    private static function canSensePlayer(DungeonMap $map, array $m, int $px, int $py): bool
    {
        $mx = (int) $m['x'];
        $my = (int) $m['y'];
        $a = (int) ($map->dTransVal[$my][$mx] ?? 0);
        $b = (int) ($map->dTransVal[$py][$px] ?? 0);
        if ($a !== 0 && $a === $b) {
            return true;
        }

        return LineClear::notSolid($map, $mx, $my, $px, $py);
    }

    /** @param array<string,mixed> $m @param callable(int,int):bool $posOk @return array<string,mixed> */
    private static function zombieAi(array $m, int $px, int $py, DungeonMap $map, Random $rng, int $dist, callable $posOk): array
    {
        if ($dist <= 1) {
            $m['mode'] = 'ATTACK';
            $m['dir'] = Direction::fromDelta($px - (int) $m['x'], $py - (int) $m['y']);

            return $m;
        }
        if ($dist > 10) {
            $m['mode'] = 'STAND';

            return $m;
        }

        return self::aiPlanWalk($m, $px, $py, $posOk);
    }

    /** @param array<string,mixed> $m @param callable(int,int):bool $posOk @param callable(int,int):bool $missOk @return array<string,mixed> */
    private static function skeletonAi(array $m, int $px, int $py, DungeonMap $map, Random $rng, int $dist, callable $posOk, callable $missOk): array
    {
        if (($m['ai'] ?? '') === 'SkeletonBow' && $dist > 1 && $dist <= 8 && $rng->generateRnd(4) === 0
            && LineClear::missile($map, (int) $m['x'], (int) $m['y'], $px, $py, $missOk)) {
            $m['mode'] = 'RANGED';
            $m['dir'] = Direction::fromDelta($px - (int) $m['x'], $py - (int) $m['y']);

            return $m;
        }
        if ($dist <= 1) {
            $m['mode'] = 'ATTACK';

            return $m;
        }

        return self::aiPlanWalk($m, $px, $py, $posOk);
    }

    /** @param array<string,mixed> $m @param callable(int,int):bool $posOk @param callable(int,int):bool $missOk @return array<string,mixed> */
    private static function goatAi(array $m, int $px, int $py, DungeonMap $map, Random $rng, int $dist, callable $posOk, callable $missOk): array
    {
        if (($m['ai'] ?? '') === 'GoatBow' && $dist > 1 && $dist <= 8 && $rng->generateRnd(4) === 0
            && LineClear::missile($map, (int) $m['x'], (int) $m['y'], $px, $py, $missOk)) {
            $m['mode'] = 'RANGED';
            $m['dir'] = Direction::fromDelta($px - (int) $m['x'], $py - (int) $m['y']);

            return $m;
        }
        if ($dist <= 1) {
            $m['mode'] = 'ATTACK';

            return $m;
        }
        if ($dist <= 6 && $rng->flipCoin(3)) {
            return self::aiPlanWalk($m, $px, $py, $posOk);
        }
        $m['mode'] = 'STAND';

        return $m;
    }

    /** @param array<string,mixed> $m @param callable(int,int):bool $posOk @return array<string,mixed> */
    private static function fallenAi(array $m, int $px, int $py, DungeonMap $map, Random $rng, int $dist, callable $posOk): array
    {
        if (($m['hp'] ?? 0) < intdiv((int) ($m['maxHp'] ?? 1), 3) && $dist < 6) {
            $tx = (int) $m['x'] + ((int) $m['x'] <=> $px);
            $ty = (int) $m['y'] + ((int) $m['y'] <=> $py);
            if ($posOk($tx, $ty) && !($tx === (int) $m['x'] && $ty === (int) $m['y'])) {
                return self::startWalk($m, $tx, $ty);
            }
        }

        return self::zombieAi($m, $px, $py, $map, $rng, $dist, $posOk);
    }

    /** @param array<string,mixed> $m @param callable(int,int):bool $posOk @return array<string,mixed> */
    private static function scavengerAi(array $m, int $px, int $py, DungeonMap $map, Random $rng, int $dist, callable $posOk): array
    {
        if ($dist > 12) {
            $m['mode'] = 'STAND';

            return $m;
        }

        return self::aiPlanWalk($m, $px, $py, $posOk);
    }

    /**
     * AiPlanWalk + WalkInDirection — set future tile, do not move tile until MonsterWalk commit.
     * @param array<string,mixed> $m @param callable(int,int):bool $posOk @return array<string,mixed>
     */
    private static function aiPlanWalk(array $m, int $px, int $py, callable $posOk): array
    {
        $pathfinder = new Path();
        $walk = static function (int $x, int $y) use ($posOk, $px, $py): bool {
            if ($x === $px && $y === $py) {
                return true;
            }

            return $posOk($x, $y);
        };
        $path = $pathfinder->findPath(
            (int) $m['x'],
            (int) $m['y'],
            $px,
            $py,
            $walk,
            null,
            12
        );
        if ($path !== [] && isset($path[0])) {
            $nx = $path[0][0];
            $ny = $path[0][1];
            if (($nx === $px && $ny === $py) || !$posOk($nx, $ny)) {
                $m['mode'] = 'STAND';
            } else {
                return self::startWalk($m, $nx, $ny);
            }
        } else {
            $m['mode'] = 'STAND';
        }

        return $m;
    }

    /**
     * WalkInDirection — tile fixed, future set, walk anim pending.
     * @param array<string,mixed> $m
     * @return array<string,mixed>
     */
    public static function startWalk(array $m, int $nx, int $ny): array
    {
        $dx = $nx - (int) $m['x'];
        $dy = $ny - (int) $m['y'];
        $m['futX'] = $nx;
        $m['futY'] = $ny;
        $m['mode'] = 'WALK';
        $m['walkFrame'] = 0;
        $m['dir'] = Direction::fromDelta($dx, $dy);

        return $m;
    }
}
