<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * info_player_deathmatch / info_player_start spawn spot.
 * Origin Z receives +9 in SelectSpawnPoint (g_client.c).
 */
final class SpawnPoint
{
    /**
     * @param list<float> $origin xyz
     * @param list<float> $angles pitch/yaw/roll degrees (yaw from angle or angles)
     */
    public function __construct(
        public readonly string $classname,
        public readonly array $origin,
        public readonly array $angles,
        public readonly int $index,
        public readonly int $spawnflags = 0,
    ) {
    }

    /** Spawn origin after Q3 +9 Z lift. @return list<float> */
    public function playerOrigin(): array
    {
        return [
            $this->origin[0],
            $this->origin[1],
            $this->origin[2] + 9.0,
        ];
    }

    /**
     * SelectInitialSpawnPoint — first info_player_deathmatch with spawnflags&1.
     *
     * @param list<self> $spawns
     */
    public static function selectInitial(array $spawns): self
    {
        foreach ($spawns as $s) {
            if (($s->spawnflags & 1) !== 0) {
                return $s;
            }
        }

        return self::selectRandomFurthest($spawns, [0.0, 0.0, 0.0]);
    }

    /**
     * SelectRandomFurthestSpawnPoint — sort by distance from avoid, pick random of farthest half.
     *
     * @param list<self> $spawns
     * @param list<float> $avoidPoint
     */
    public static function selectRandomFurthest(array $spawns, array $avoidPoint): self
    {
        if ($spawns === []) {
            throw new \RuntimeException('no spawn points');
        }
        $ranked = $spawns;
        usort($ranked, static function (self $a, self $b) use ($avoidPoint): int {
            $da = self::distSq($a->origin, $avoidPoint);
            $db = self::distSq($b->origin, $avoidPoint);

            return $db <=> $da;
        });
        $half = intdiv(count($ranked), 2);
        $rnd = $half > 0 ? random_int(0, $half - 1) : 0;

        return $ranked[$rnd];
    }

    /** @param list<float> $a @param list<float> $b */
    private static function distSq(array $a, array $b): float
    {
        $dx = $a[0] - $b[0];
        $dy = $a[1] - $b[1];
        $dz = $a[2] - $b[2];

        return $dx * $dx + $dy * $dy + $dz * $dz;
    }
}
