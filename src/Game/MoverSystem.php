<?php

declare(strict_types=1);

namespace Quake3\Game;

use Quake3\Bsp\BspBinary;
use Quake3\Bsp\BspIdent;
use Quake3\Bsp\BspMap;

/**
 * func_door + func_rotating (g_mover.c subset for q3dm0).
 */
final class MoverSystem
{
    /** @var list<array{
     *   type: string,
     *   mins: list<float>, maxs: list<float>,
     *   pos1: list<float>, pos2: list<float>,
     *   origin: list<float>, angles: list<float>,
     *   speed: float, wait: float, lip: float,
     *   phase: string, timer: float, angleSpeed: float, axis: int,
     *   model2: ?string, modelIndex: int, team: string
     * }> */
    public array $movers = [];

    /**
     * Current solid AABBs for CollisionModel::setEntityBoxes (doors etc.).
     * @var list<array{mins: list<float>, maxs: list<float>}>
     */
    public array $solids = [];

    public static function load(BspMap $map): self
    {
        $sys = new self();
        $models = self::loadModelBounds($map);
        $ents = (new EntityParser())->parseEntities($map->readLumpBytes(BspIdent::LUMP_ENTITIES));

        foreach ($ents as $e) {
            $cn = $e['classname'] ?? '';
            if ($cn !== 'func_door' && $cn !== 'func_rotating') {
                continue;
            }
            $model = $e['model'] ?? '';
            $mins = [-8.0, -8.0, -8.0];
            $maxs = [8.0, 8.0, 8.0];
            $modelIndex = 0;
            if (preg_match('/^\*(\d+)$/', $model, $m) && isset($models[(int) $m[1]])) {
                $modelIndex = (int) $m[1];
                $mins = $models[$modelIndex]['mins'];
                $maxs = $models[$modelIndex]['maxs'];
            }
            $origin = [
                ($mins[0] + $maxs[0]) * 0.5,
                ($mins[1] + $maxs[1]) * 0.5,
                ($mins[2] + $maxs[2]) * 0.5,
            ];
            $size = [
                $maxs[0] - $mins[0],
                $maxs[1] - $mins[1],
                $maxs[2] - $mins[2],
            ];

            if ($cn === 'func_door') {
                $speed = isset($e['speed']) ? (float) $e['speed'] : 400.0;
                $wait = isset($e['wait']) ? (float) $e['wait'] : 2.0;
                $lip = isset($e['lip']) ? (float) $e['lip'] : 8.0;
                $movedir = self::movedirFromAngles($e);
                $distance = abs($movedir[0]) * $size[0]
                    + abs($movedir[1]) * $size[1]
                    + abs($movedir[2]) * $size[2]
                    - $lip;
                $pos1 = $origin;
                $pos2 = [
                    $origin[0] + $movedir[0] * $distance,
                    $origin[1] + $movedir[1] * $distance,
                    $origin[2] + $movedir[2] * $distance,
                ];
                $sys->movers[] = [
                    'type' => 'door',
                    'mins' => $mins,
                    'maxs' => $maxs,
                    'pos1' => $pos1,
                    'pos2' => $pos2,
                    'origin' => $origin,
                    'angles' => [0.0, 0.0, 0.0],
                    'speed' => $speed,
                    'wait' => $wait,
                    'lip' => $lip,
                    'phase' => 'closed',
                    'timer' => 0.0,
                    'angleSpeed' => 0.0,
                    'axis' => 1,
                    'model2' => null,
                    'modelIndex' => $modelIndex,
                    'team' => (string) ($e['team'] ?? ''),
                ];
            } else {
                $speed = isset($e['speed']) ? (float) $e['speed'] : 100.0;
                $spawnflags = (int) ($e['spawnflags'] ?? 0);
                $axis = 1; // Y
                if ($spawnflags & 4) {
                    $axis = 2; // Z
                } elseif ($spawnflags & 8) {
                    $axis = 0; // X
                }
                // SP_func_rotating: spin/draw pivot is entity origin key (not AABB mid)
                $pivot = $origin;
                if (isset($e['origin']) && $e['origin'] !== '') {
                    $p = preg_split('/\s+/', trim($e['origin'])) ?: [];
                    $pivot = [
                        (float) ($p[0] ?? 0),
                        (float) ($p[1] ?? 0),
                        (float) ($p[2] ?? 0),
                    ];
                }
                $model2 = isset($e['model2']) && $e['model2'] !== '' ? $e['model2'] : null;
                $sys->movers[] = [
                    'type' => 'rotating',
                    'mins' => $mins,
                    'maxs' => $maxs,
                    'pos1' => $pivot,
                    'pos2' => $pivot,
                    'origin' => $pivot,
                    'angles' => [0.0, 0.0, 0.0],
                    'speed' => $speed,
                    'wait' => 0.0,
                    'lip' => 0.0,
                    'phase' => 'spin',
                    'timer' => 0.0,
                    'angleSpeed' => $speed,
                    'axis' => $axis,
                    'model2' => $model2,
                    'modelIndex' => $modelIndex,
                    'team' => '',
                ];
            }
        }

        $sys->refreshSolids();
        return $sys;
    }

    /** @param array<string, string> $e @return list<float> */
    private static function movedirFromAngles(array $e): array
    {
        $angles = [0.0, 0.0, 0.0];
        if (isset($e['angles'])) {
            $p = preg_split('/\s+/', trim($e['angles'])) ?: [];
            $angles = [(float) ($p[0] ?? 0), (float) ($p[1] ?? 0), (float) ($p[2] ?? 0)];
        } elseif (isset($e['angle'])) {
            $angles[1] = (float) $e['angle'];
        }
        // special up/down
        if ($angles[0] === 0.0 && $angles[1] === -1.0 && $angles[2] === 0.0) {
            return [0.0, 0.0, 1.0];
        }
        if ($angles[0] === 0.0 && $angles[1] === -2.0 && $angles[2] === 0.0) {
            return [0.0, 0.0, -1.0];
        }
        $yaw = $angles[1] * M_PI / 180.0;
        return [cos($yaw), sin($yaw), 0.0];
    }

    /**
     * @return array<int, array{mins: list<float>, maxs: list<float>}>
     */
    private static function loadModelBounds(BspMap $map): array
    {
        $bytes = $map->readLumpBytes(BspIdent::LUMP_MODELS);
        $size = 40;
        $count = intdiv(strlen($bytes), $size);
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $o = $i * $size;
            $out[$i] = [
                'mins' => [
                    BspBinary::float32($bytes, $o) - 1,
                    BspBinary::float32($bytes, $o + 4) - 1,
                    BspBinary::float32($bytes, $o + 8) - 1,
                ],
                'maxs' => [
                    BspBinary::float32($bytes, $o + 12) + 1,
                    BspBinary::float32($bytes, $o + 16) + 1,
                    BspBinary::float32($bytes, $o + 20) + 1,
                ],
            ];
        }
        return $out;
    }

    /**
     * Advance movers; open doors when player near (MatchTeam for teamed double doors).
     * @return list<string> events (door_start / door_end)
     */
    public function update(Player $player, float $dt): array
    {
        $events = [];
        foreach ($this->movers as &$m) {
            if ($m['type'] === 'rotating') {
                $m['angles'][$m['axis']] += $m['angleSpeed'] * $dt;
            }
        }
        unset($m);

        /** @var array<string, list<int>> */
        $groups = [];
        foreach ($this->movers as $i => $m) {
            if ($m['type'] !== 'door') {
                continue;
            }
            $key = $m['team'] !== '' ? ('t:' . $m['team']) : ('i:' . $i);
            $groups[$key][] = $i;
        }

        foreach ($groups as $indices) {
            $master = $indices[0];
            $near = $this->doorTeamNear($indices, $player);
            $ev = $this->advanceDoorMaster($master, $near, $dt);
            // MatchTeam — slaves share phase/timer; each interpolates own pos1→pos2
            $phase = $this->movers[$master]['phase'];
            $timer = $this->movers[$master]['timer'];
            foreach ($indices as $i) {
                if ($i === $master) {
                    continue;
                }
                $this->movers[$i]['phase'] = $phase;
                $this->movers[$i]['timer'] = $timer;
                $this->advanceDoorPose($i, $dt);
            }
            foreach ($ev as $e) {
                $events[] = $e;
            }
        }

        $this->refreshSolids();
        return $events;
    }

    /**
     * Think_SpawnNewDoorTrigger — combined closed AABBs, expand thinnest axis ±120.
     * @param list<int> $indices
     */
    private function doorTeamNear(array $indices, Player $player): bool
    {
        $mins = [INF, INF, INF];
        $maxs = [-INF, -INF, -INF];
        foreach ($indices as $i) {
            $m = $this->movers[$i];
            for ($a = 0; $a < 3; $a++) {
                $mins[$a] = min($mins[$a], $m['mins'][$a]);
                $maxs[$a] = max($maxs[$a], $m['maxs'][$a]);
            }
        }
        $best = 0;
        for ($a = 1; $a < 3; $a++) {
            if (($maxs[$a] - $mins[$a]) < ($maxs[$best] - $mins[$best])) {
                $best = $a;
            }
        }
        $mins[$best] -= 120.0;
        $maxs[$best] += 120.0;
        $o = $player->origin;
        $pmins = $player->mins;
        $pmaxs = $player->maxs;
        return !($o[0] + $pmaxs[0] < $mins[0]
            || $o[0] + $pmins[0] > $maxs[0]
            || $o[1] + $pmaxs[1] < $mins[1]
            || $o[1] + $pmins[1] > $maxs[1]
            || $o[2] + $pmaxs[2] < $mins[2]
            || $o[2] + $pmins[2] > $maxs[2]);
    }

    /** @return list<string> */
    private function advanceDoorMaster(int $i, bool $near, float $dt): array
    {
        $events = [];
        $m = &$this->movers[$i];

        if ($m['phase'] === 'closed' && $near) {
            $m['phase'] = 'opening';
            $events[] = 'door_start';
        }
        if ($m['phase'] === 'opening') {
            if ($this->stepDoorToward($i, $m['pos2'], $dt)) {
                $m['origin'] = $m['pos2'];
                $m['phase'] = 'open';
                $m['timer'] = $m['wait'];
                $events[] = 'door_end';
            }
        } elseif ($m['phase'] === 'open') {
            // Use_BinaryMover POS2: each trigger touch resets nextthink = now + wait
            if ($near) {
                $m['timer'] = $m['wait'];
            } else {
                $m['timer'] -= $dt;
                if ($m['timer'] <= 0) {
                    $m['phase'] = 'closing';
                    $events[] = 'door_start';
                }
            }
        } elseif ($m['phase'] === 'closing') {
            // Touch_DoorTrigger / Use_BinaryMover: reverse mid-close when player near
            if ($near) {
                $m['phase'] = 'opening';
                $events[] = 'door_start';
                if ($this->stepDoorToward($i, $m['pos2'], $dt)) {
                    $m['origin'] = $m['pos2'];
                    $m['phase'] = 'open';
                    $m['timer'] = $m['wait'];
                    $events[] = 'door_end';
                }
            } elseif ($this->stepDoorToward($i, $m['pos1'], $dt)) {
                $m['origin'] = $m['pos1'];
                $m['phase'] = 'closed';
                $events[] = 'door_end';
            }
        }
        unset($m);
        return $events;
    }

    /** Slave pose only — phase already synced from master. */
    private function advanceDoorPose(int $i, float $dt): void
    {
        $m = &$this->movers[$i];
        if ($m['phase'] === 'opening') {
            if ($this->stepDoorToward($i, $m['pos2'], $dt)) {
                $m['origin'] = $m['pos2'];
            }
        } elseif ($m['phase'] === 'open') {
            $m['origin'] = $m['pos2'];
        } elseif ($m['phase'] === 'closing') {
            if ($this->stepDoorToward($i, $m['pos1'], $dt)) {
                $m['origin'] = $m['pos1'];
            }
        } elseif ($m['phase'] === 'closed') {
            $m['origin'] = $m['pos1'];
        }
        unset($m);
    }

    /** @param list<float> $to @return bool arrived */
    private function stepDoorToward(int $i, array $to, float $dt): bool
    {
        $m = &$this->movers[$i];
        $cur = $m['origin'];
        $remain = [$to[0] - $cur[0], $to[1] - $cur[1], $to[2] - $cur[2]];
        $rd = sqrt($remain[0] ** 2 + $remain[1] ** 2 + $remain[2] ** 2);
        $step = $m['speed'] * $dt;
        if ($rd <= $step || $rd < 0.5) {
            unset($m);
            return true;
        }
        $m['origin'][0] += $remain[0] / $rd * $step;
        $m['origin'][1] += $remain[1] / $rd * $step;
        $m['origin'][2] += $remain[2] / $rd * $step;
        unset($m);
        return false;
    }

    /** Rebuild $solids from current mover poses (doors only — rotating brushes are tiny local AABBs). */
    private function refreshSolids(): void
    {
        $solids = [];
        foreach ($this->movers as $m) {
            if ($m['type'] !== 'door') {
                continue;
            }
            $delta = [
                $m['origin'][0] - $m['pos1'][0],
                $m['origin'][1] - $m['pos1'][1],
                $m['origin'][2] - $m['pos1'][2],
            ];
            $solids[] = [
                'mins' => [$m['mins'][0] + $delta[0], $m['mins'][1] + $delta[1], $m['mins'][2] + $delta[2]],
                'maxs' => [$m['maxs'][0] + $delta[0], $m['maxs'][1] + $delta[1], $m['maxs'][2] + $delta[2]],
            ];
        }
        $this->solids = $solids;
    }
}
