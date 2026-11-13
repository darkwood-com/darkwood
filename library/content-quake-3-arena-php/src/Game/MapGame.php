<?php

declare(strict_types=1);

namespace Quake3\Game;

use Quake3\Bsp\BspBinary;
use Quake3\Bsp\BspIdent;
use Quake3\Bsp\BspMap;
use Quake3\Collision\CollisionModel;
use Quake3\Filesystem\FileSystem;
use Quake3\Render\MarkFragments;
use Quake3\Render\Md3Model;

/**
 * Map entities: items (MD3), teleporter, movers, weapons + plasma.
 */
final class MapGame
{
    /** classname => primary md3 path */
    public const ITEM_MODELS = [
        'item_health_small' => 'models/powerups/health/small_cross.md3',
        'item_health' => 'models/powerups/health/medium_cross.md3',
        'item_health_large' => 'models/powerups/health/large_cross.md3',
        'item_armor_shard' => 'models/powerups/armor/shard.md3',
        'item_armor_combat' => 'models/powerups/armor/armor_yel.md3',
        'weapon_shotgun' => 'models/weapons2/shotgun/shotgun.md3',
        'weapon_plasmagun' => 'models/weapons2/plasma/plasma.md3',
        'ammo_shells' => 'models/powerups/ammo/shotgunam.md3',
        'ammo_bullets' => 'models/powerups/ammo/machinegunam.md3',
        'ammo_cells' => 'models/powerups/ammo/plasmaam.md3',
    ];

    /** IT_HEALTH models[1] companion spheres (bg_misc.c) */
    public const ITEM_SPHERE_MODELS = [
        'item_health_small' => 'models/powerups/health/small_sphere.md3',
        'item_health' => 'models/powerups/health/medium_sphere.md3',
        'item_health_large' => 'models/powerups/health/large_sphere.md3',
    ];

    /** @var list<array{classname: string, origin: list<float>, angles: list<float>, target: string, targetname: string, model: string, mins: list<float>, maxs: list<float>, active: bool, respawnAt: float, wait: float, md3Path: string, md3: ?Md3Model, md3Sphere: ?Md3Model}> */
    public array $items = [];

    /** @var list<array{mins: list<float>, maxs: list<float>, target: string}> */
    public array $teleports = [];

    /**
     * target_speaker with spawnflags&1 (always-on loopSound) — cg_ents trap_S_AddLoopingSound.
     * @var list<array{origin: list<float>, noise: string}>
     */
    public array $loopSpeakers = [];

    /**
     * target_speaker keyed by targetname (oneshots fired by func_timer / G_UseTargets).
     * @var array<string, list<array{origin: list<float>, noise: string}>>
     */
    public array $namedSpeakers = [];

    /**
     * func_timer — START_ON or activated by timerTriggers.
     * @var list<array{target: string, targetname: string, wait: float, random: float, nextFire: float, active: bool}>
     */
    public array $timers = [];

    /**
     * trigger_multiple that targets a func_timer targetname (dormant → use).
     * @var list<array{mins: list<float>, maxs: list<float>, target: string, wait: float, active: bool}>
     */
    public array $timerTriggers = [];

    /**
     * misc_portal_surface resolved views (CG_Portal / locateCamera).
     * @var list<array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool, roll: float, swing: bool, rotateSpeed: int}>
     */
    public array $portalSurfaces = [];

    /** @var array<string, array{origin: list<float>, angles: list<float>}> */
    public array $targets = [];

    /** @var list<array{origin: list<float>, mins: list<float>, maxs: list<float>, health: int, alive: bool}> */
    public array $dummies = [];

    /** @var list<Projectile> */
    public array $projectiles = [];

    /** @var list<array{origin: list<float>, normal: list<float>, color: list<float>, life: float}> */
    public array $pendingImpacts = [];

    public MoverSystem $movers;
    public WeaponState $weaponState;
    public float $time = 0.0;

    private function __construct()
    {
        $this->movers = new MoverSystem();
        $this->weaponState = new WeaponState();
    }

    public static function load(BspMap $map, ?FileSystem $fs = null): self
    {
        $g = new self();
        $g->movers = MoverSystem::load($map);
        $models = self::loadModelBounds($map);
        $ents = (new EntityParser())->parseEntities($map->readLumpBytes(BspIdent::LUMP_ENTITIES));
        $portalSurfaceEnts = [];
        $portalCameras = [];
        /** @var list<array{mins: list<float>, maxs: list<float>, target: string, wait: float}> */
        $pendingTimerTriggers = [];

        foreach ($ents as $e) {
            $cn = $e['classname'] ?? '';
            $origin = self::vec3($e['origin'] ?? '0 0 0');
            $angles = [0.0, 0.0, 0.0];
            if (isset($e['angles'])) {
                $angles = self::vec3($e['angles']);
            } elseif (isset($e['angle'])) {
                $angles[1] = (float) $e['angle'];
            }
            $target = $e['target'] ?? '';
            $targetname = $e['targetname'] ?? '';

            if ($targetname !== '') {
                $g->targets[$targetname] = ['origin' => $origin, 'angles' => $angles];
            }

            if (str_starts_with($cn, 'item_') || str_starts_with($cn, 'weapon_') || str_starts_with($cn, 'ammo_')) {
                $md3Path = self::ITEM_MODELS[$cn] ?? '';
                $md3 = null;
                if ($fs !== null && $md3Path !== '' && $fs->exists($md3Path)) {
                    try {
                        $md3 = Md3Model::load($fs, $md3Path);
                    } catch (\Throwable) {
                        $md3 = null;
                    }
                }
                $spherePath = self::ITEM_SPHERE_MODELS[$cn] ?? '';
                $md3Sphere = null;
                if ($fs !== null && $spherePath !== '' && $fs->exists($spherePath)) {
                    try {
                        $md3Sphere = Md3Model::load($fs, $spherePath);
                    } catch (\Throwable) {
                        $md3Sphere = null;
                    }
                }
                $g->items[] = [
                    'classname' => $cn,
                    'origin' => $origin,
                    'angles' => $angles,
                    'target' => $target,
                    'targetname' => $targetname,
                    'model' => $e['model'] ?? '',
                    'mins' => [$origin[0] - 15, $origin[1] - 15, $origin[2] - 15],
                    'maxs' => [$origin[0] + 15, $origin[1] + 15, $origin[2] + 15],
                    'active' => true,
                    'respawnAt' => 0.0,
                    'miscTime' => 0.0,
                    'wait' => match (true) {
                        str_starts_with($cn, 'weapon_') => 5.0,   // RESPAWN_WEAPON / RegisterItem
                        str_starts_with($cn, 'item_armor') => 25.0, // RESPAWN_ARMOR
                        str_starts_with($cn, 'item_health') => 35.0, // RESPAWN_HEALTH
                        str_starts_with($cn, 'ammo_') => 40.0,     // RESPAWN_AMMO
                        default => 30.0,
                    },
                    'md3Path' => $md3Path,
                    'md3' => $md3,
                    'md3Sphere' => $md3Sphere,
                ];
                if ($cn === 'weapon_shotgun' && $g->dummies === []) {
                    $g->dummies[] = [
                        'origin' => $origin,
                        'mins' => [$origin[0] - 20, $origin[1] - 20, $origin[2] - 24],
                        'maxs' => [$origin[0] + 20, $origin[1] + 20, $origin[2] + 40],
                        'health' => 50,
                        'alive' => true,
                    ];
                }
            }

            if ($cn === 'trigger_teleport') {
                $model = $e['model'] ?? '';
                $mins = [-8.0, -8.0, -8.0];
                $maxs = [8.0, 8.0, 8.0];
                if (preg_match('/^\*(\d+)$/', $model, $m)) {
                    $idx = (int) $m[1];
                    if (isset($models[$idx])) {
                        $mins = $models[$idx]['mins'];
                        $maxs = $models[$idx]['maxs'];
                    }
                }
                $g->teleports[] = [
                    'mins' => $mins,
                    'maxs' => $maxs,
                    'target' => $target,
                ];
            }

            // SP_target_speaker: spawnflags&1 → s.loopSound on at spawn; named for G_UseTargets
            if ($cn === 'target_speaker') {
                $noise = (string) ($e['noise'] ?? '');
                if ($noise !== '') {
                    if (!str_ends_with(strtolower($noise), '.wav')) {
                        $noise .= '.wav';
                    }
                    if ((((int) ($e['spawnflags'] ?? 0)) & 1) !== 0) {
                        $g->loopSpeakers[] = [
                            'origin' => $origin,
                            'noise' => $noise,
                        ];
                    }
                    if ($targetname !== '') {
                        $g->namedSpeakers[$targetname][] = [
                            'origin' => $origin,
                            'noise' => $noise,
                        ];
                    }
                }
            }

            // SP_func_timer — START_ON or dormant until trigger_multiple use
            if ($cn === 'func_timer' && $target !== '') {
                $wait = (float) ($e['wait'] ?? 1);
                $random = (float) ($e['random'] ?? 1);
                if ($random >= $wait) {
                    $random = max(0.0, $wait - 0.1);
                }
                $startOn = (((int) ($e['spawnflags'] ?? 0)) & 1) !== 0;
                $g->timers[] = [
                    'target' => $target,
                    'targetname' => $targetname,
                    'wait' => $wait,
                    'random' => $random,
                    'active' => $startOn,
                    'nextFire' => $startOn ? 0.1 : 0.0,
                ];
            }

            // trigger_multiple → func_timer only (filtered after parse; no intro VO / target_delay)
            if ($cn === 'trigger_multiple' && $target !== '') {
                $model = $e['model'] ?? '';
                $mins = [-8.0, -8.0, -8.0];
                $maxs = [8.0, 8.0, 8.0];
                if (preg_match('/^\*(\d+)$/', $model, $m)) {
                    $idx = (int) $m[1];
                    if (isset($models[$idx])) {
                        $mins = $models[$idx]['mins'];
                        $maxs = $models[$idx]['maxs'];
                    }
                }
                $pendingTimerTriggers[] = [
                    'mins' => $mins,
                    'maxs' => $maxs,
                    'target' => $target,
                    'wait' => (float) ($e['wait'] ?? 0.2),
                ];
            }

            if ($cn === 'misc_portal_surface') {
                $portalSurfaceEnts[] = [
                    'origin' => $origin,
                    'target' => $target,
                ];
            }
            if ($cn === 'misc_portal_camera') {
                $spawnflags = (int) ($e['spawnflags'] ?? 0);
                $rotateSpeed = 0;
                if (($spawnflags & 1) !== 0) {
                    $rotateSpeed = 25;
                } elseif (($spawnflags & 2) !== 0) {
                    $rotateSpeed = 75;
                }
                // bit 4 = noswing → powerups=0; else swing (default)
                $swing = ($spawnflags & 4) === 0;
                $portalCameras[$targetname !== '' ? $targetname : ('_cam' . count($portalCameras))] = [
                    'origin' => $origin,
                    'angles' => $angles,
                    'target' => $target,
                    'targetname' => $targetname,
                    'roll' => (float) ($e['roll'] ?? 0),
                    'swing' => $swing,
                    'rotateSpeed' => $rotateSpeed,
                ];
            }
        }

        $timerNames = [];
        foreach ($g->timers as $tm) {
            if ($tm['targetname'] !== '') {
                $timerNames[$tm['targetname']] = true;
            }
        }
        foreach ($pendingTimerTriggers as $tr) {
            if (!isset($timerNames[$tr['target']])) {
                continue; // skip intro VO / target_delay chains
            }
            $g->timerTriggers[] = [
                'mins' => $tr['mins'],
                'maxs' => $tr['maxs'],
                'target' => $tr['target'],
                'wait' => $tr['wait'],
                'active' => true,
            ];
        }

        $g->portalSurfaces = self::resolvePortalSurfaces($portalSurfaceEnts, $portalCameras, $g->targets);

        return $g;
    }

    /**
     * @param list<array{origin: list<float>, target: string}> $surfaces
     * @param array<string, array{origin: list<float>, angles: list<float>, target: string, targetname: string, roll?: float, swing?: bool, rotateSpeed?: int}> $cameras
     * @param array<string, array{origin: list<float>, angles: list<float>}> $targets
     * @return list<array{origin: list<float>, origin2: list<float>, axis: list<list<float>>, mirror: bool, roll: float, swing: bool, rotateSpeed: int}>
     */
    private static function resolvePortalSurfaces(array $surfaces, array $cameras, array $targets): array
    {
        $out = [];
        foreach ($surfaces as $surf) {
            $origin = $surf['origin'];
            $target = $surf['target'];
            if ($target === '') {
                // Untargeted → pure mirror (origin2 = origin)
                $out[] = [
                    'origin' => $origin,
                    'origin2' => $origin,
                    'axis' => [[1.0, 0.0, 0.0], [0.0, 1.0, 0.0], [0.0, 0.0, 1.0]],
                    'mirror' => true,
                    'roll' => 0.0,
                    'swing' => false,
                    'rotateSpeed' => 0,
                ];
                continue;
            }
            $cam = null;
            foreach ($cameras as $c) {
                if (($c['targetname'] ?? '') === $target) {
                    $cam = $c;
                    break;
                }
            }
            if ($cam === null && isset($targets[$target])) {
                // Fallback: target name points at any entity origin/angles
                $cam = [
                    'origin' => $targets[$target]['origin'],
                    'angles' => $targets[$target]['angles'],
                    'target' => '',
                    'targetname' => $target,
                    'roll' => 0.0,
                    'swing' => true,
                    'rotateSpeed' => 0,
                ];
            }
            if ($cam === null) {
                continue;
            }
            $origin2 = $cam['origin'];
            $lookTarget = $cam['target'] ?? '';
            if ($lookTarget !== '' && isset($targets[$lookTarget])) {
                $to = $targets[$lookTarget]['origin'];
                $dir = [$to[0] - $origin2[0], $to[1] - $origin2[1], $to[2] - $origin2[2]];
                $len = sqrt($dir[0] ** 2 + $dir[1] ** 2 + $dir[2] ** 2) ?: 1.0;
                $dir = [$dir[0] / $len, $dir[1] / $len, $dir[2] / $len];
            } else {
                [$dir] = QuakeMath::angleVectors($cam['angles']);
            }
            // CG_Portal: axis[0]=dir, axis[1]=−Perp(dir), axis[2]=Cross
            $perp = self::perpendicular($dir);
            $axis1 = [-$perp[0], -$perp[1], -$perp[2]];
            $axis2 = [
                $dir[1] * $axis1[2] - $dir[2] * $axis1[1],
                $dir[2] * $axis1[0] - $dir[0] * $axis1[2],
                $dir[0] * $axis1[1] - $dir[1] * $axis1[0],
            ];
            $out[] = [
                'origin' => $origin,
                'origin2' => $origin2,
                'axis' => [$dir, $axis1, $axis2],
                'mirror' => false,
                'roll' => (float) ($cam['roll'] ?? 0.0),
                'swing' => (bool) ($cam['swing'] ?? true),
                'rotateSpeed' => (int) ($cam['rotateSpeed'] ?? 0),
            ];
        }

        return $out;
    }

    /** @param list<float> $n @return list<float> */
    private static function perpendicular(array $n): array
    {
        $minelem = 1.0;
        $pos = 0;
        for ($i = 0; $i < 3; $i++) {
            $a = abs($n[$i]);
            if ($a < $minelem) {
                $minelem = $a;
                $pos = $i;
            }
        }
        $temp = [0.0, 0.0, 0.0];
        $temp[$pos] = 1.0;
        $d = $temp[0] * $n[0] + $temp[1] * $n[1] + $temp[2] * $n[2];
        $out = [$temp[0] - $d * $n[0], $temp[1] - $d * $n[1], $temp[2] - $d * $n[2]];
        $len = sqrt($out[0] ** 2 + $out[1] ** 2 + $out[2] ** 2) ?: 1.0;

        return [$out[0] / $len, $out[1] / $len, $out[2] / $len];
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

    /** @return list<float> */
    private static function vec3(string $s): array
    {
        $p = preg_split('/\s+/', trim($s)) ?: [];
        return [(float) ($p[0] ?? 0), (float) ($p[1] ?? 0), (float) ($p[2] ?? 0)];
    }

    /**
     * @return list<string> events
     */
    public function update(Player $player, float $dt, CollisionModel $cm): array
    {
        $this->time += $dt;
        $events = [];
        $player->timerActions($dt);
        $this->weaponState->update($player, $dt);
        foreach ($this->movers->update($player, $dt) as $ev) {
            $events[] = $ev;
        }

        $this->runDroppedItems($cm, $dt);

        foreach ($this->items as &$it) {
            if (!empty($it['dropped']) && $it['active'] && ($it['despawnAt'] ?? 0) > 0 && $this->time >= $it['despawnAt']) {
                $it['active'] = false;
                $it['respawnAt'] = 0.0;
                continue;
            }
            if (!$it['active'] && $it['respawnAt'] > 0 && $this->time >= $it['respawnAt']) {
                $it['active'] = true;
                $it['respawnAt'] = 0.0;
                $it['miscTime'] = $this->time; // EV_ITEM_RESPAWN → ITEM_SCALEUP_TIME
                $events[] = 'respawn ' . $it['classname'];
            }
            if (!$it['active']) {
                continue;
            }
            if ($this->playerTouchesItem($player->origin, $it['origin'])) {
                if (!$player->alive || $player->health < 1) {
                    continue;
                }
                // BG_CanItemBeGrabbed — refuse when full (don't consume + start wait for +0)
                if (!$this->canItemBeGrabbed($player, $it['classname'])) {
                    continue;
                }
                $it['active'] = false;
                if (!empty($it['dropped'])) {
                    $it['respawnAt'] = 0.0;
                    $it['despawnAt'] = 0.0;
                } else {
                    $it['respawnAt'] = $this->time + $it['wait'];
                }
                $events[] = 'pickup ' . $it['classname'];
                $this->applyPickup($player, $it);
            }
        }
        unset($it);

        // trigger_multiple → func_timer_use (dormant timers → start; wait=-1 one-shot)
        foreach ($this->timerTriggers as &$tr) {
            if (!$tr['active']) {
                continue;
            }
            if (!$this->aabbTouch($player->origin, Player::MINS, Player::MAXS, $tr['mins'], $tr['maxs'])) {
                continue;
            }
            foreach ($this->timers as &$tm) {
                if ($tm['targetname'] !== $tr['target']) {
                    continue;
                }
                if ($tm['active']) {
                    // already on → turn off (func_timer_use)
                    $tm['active'] = false;
                } else {
                    $tm['active'] = true;
                    foreach ($this->namedSpeakers[$tm['target']] ?? [] as $spk) {
                        $events[] = 'speaker ' . $spk['noise'];
                    }
                    $crandom = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
                    $delay = $tm['wait'] + $crandom * $tm['random'];
                    if ($delay < 0.1) {
                        $delay = 0.1;
                    }
                    $tm['nextFire'] = $this->time + $delay;
                }
                break;
            }
            unset($tm);
            // wait < 0 → fire once then remove (multi_trigger)
            if ($tr['wait'] < 0.0) {
                $tr['active'] = false;
            }
        }
        unset($tr);

        foreach ($this->timers as &$tm) {
            if (!$tm['active'] || $this->time < $tm['nextFire']) {
                continue;
            }
            foreach ($this->namedSpeakers[$tm['target']] ?? [] as $spk) {
                $events[] = 'speaker ' . $spk['noise'];
            }
            // func_timer_think: wait + crandom()*random
            $crandom = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $delay = $tm['wait'] + $crandom * $tm['random'];
            if ($delay < 0.1) {
                $delay = 0.1;
            }
            $tm['nextFire'] = $this->time + $delay;
        }
        unset($tm);

        foreach ($this->teleports as $tp) {
            if ($this->aabbTouch($player->origin, Player::MINS, Player::MAXS, $tp['mins'], $tp['maxs'])) {
                $dest = $this->targets[$tp['target']] ?? null;
                if ($dest !== null) {
                    $old = $player->origin;
                    // TeleportPlayer: origin+1 Z, spit velocity 400 along dest angles
                    $player->origin = [
                        $dest['origin'][0],
                        $dest['origin'][1],
                        $dest['origin'][2] + 1.0,
                    ];
                    $fwd = $this->forward($dest['angles']);
                    $player->velocity = [$fwd[0] * 400.0, $fwd[1] * 400.0, $fwd[2] * 400.0];
                    $player->viewangles = $dest['angles'];
                    $player->pmTime = 160;
                    $player->pmFlags |= Player::PMF_TIME_KNOCKBACK; // TeleportPlayer air-accel hold
                    $events[] = sprintf('teleport_out %.3f %.3f %.3f', $old[0], $old[1], $old[2]);
                    $events[] = sprintf(
                        'teleport_in %.3f %.3f %.3f',
                        $player->origin[0],
                        $player->origin[1],
                        $player->origin[2],
                    );
                }
            }
        }

        $events = array_merge($events, $this->updateProjectiles($player, $cm, $dt));

        return $events;
    }

    /** @return list<string> */
    private function updateProjectiles(Player $player, CollisionModel $cm, float $dt): array
    {
        $events = [];
        foreach ($this->projectiles as $i => $p) {
            if (!$p->alive) {
                continue;
            }
            if ($this->time >= $p->dieAt) {
                $p->alive = false;
                continue;
            }
            $start = $p->origin;
            $end = [
                $start[0] + $p->velocity[0] * $dt,
                $start[1] + $p->velocity[1] * $dt,
                $start[2] + $p->velocity[2] * $dt,
            ];
            $tr = $cm->trace($start, $end, [0, 0, 0], [0, 0, 0], CollisionModel::MASK_SHOT);
            $p->origin = $tr->endPos;

            // hit dummy?
            foreach ($this->dummies as &$d) {
                if (!$d['alive']) {
                    continue;
                }
                if ($this->pointInAabb($p->origin, $d['mins'], $d['maxs'])) {
                    $d['health'] -= Projectile::DAMAGE;
                    $events[] = 'plasma hit dummy';
                    $p->alive = false;
                    $this->pendingImpacts[] = [
                        'origin' => $p->origin,
                        'normal' => [0.0, 0.0, 1.0],
                        'color' => [0.2, 0.7, 1.0],
                        'life' => 0.6,
                    ];
                    if ($d['health'] <= 0) {
                        $d['alive'] = false;
                    }
                    $this->applySplash($player, $p->origin, Projectile::SPLASH_DAMAGE, Projectile::SPLASH_RADIUS);
                    break;
                }
            }
            unset($d);

            if ($p->alive && $tr->fraction < 1.0) {
                $p->alive = false;
                // G_MissileImpact: SURF_NOIMPACT (sky) → free, no FX/splash
                if (($tr->surfaceFlags & MarkFragments::SURF_NOIMPACT) !== 0) {
                    continue;
                }
                $events[] = 'plasma impact';
                $this->pendingImpacts[] = [
                    'origin' => $p->origin,
                    'normal' => $tr->planeNormal ?? [0.0, 0.0, 1.0],
                    'color' => [0.2, 0.7, 1.0],
                    'life' => 0.6,
                ];
                $this->applySplash($player, $p->origin, Projectile::SPLASH_DAMAGE, Projectile::SPLASH_RADIUS);
            }
        }
        // prune
        $this->projectiles = array_values(array_filter($this->projectiles, static fn (Projectile $p) => $p->alive));
        return $events;
    }

    /** @param list<float> $point @param list<float> $mins @param list<float> $maxs */
    private function pointInAabb(array $point, array $mins, array $maxs): bool
    {
        return $point[0] >= $mins[0] && $point[0] <= $maxs[0]
            && $point[1] >= $mins[1] && $point[1] <= $maxs[1]
            && $point[2] >= $mins[2] && $point[2] <= $maxs[2];
    }

    /** G_RadiusDamage — dist to AABB edge (not origin center); knockback dir Z+24. @param list<float> $origin */
    private function applySplash(Player $player, array $origin, int $damage, float $radius): void
    {
        $absmin = [
            $player->origin[0] + Player::MINS[0],
            $player->origin[1] + Player::MINS[1],
            $player->origin[2] + Player::MINS[2],
        ];
        $absmax = [
            $player->origin[0] + Player::MAXS[0],
            $player->origin[1] + Player::MAXS[1],
            $player->origin[2] + Player::MAXS[2],
        ];
        $v = [0.0, 0.0, 0.0];
        for ($i = 0; $i < 3; $i++) {
            if ($origin[$i] < $absmin[$i]) {
                $v[$i] = $absmin[$i] - $origin[$i];
            } elseif ($origin[$i] > $absmax[$i]) {
                $v[$i] = $origin[$i] - $absmax[$i];
            }
        }
        $dist = sqrt($v[0] * $v[0] + $v[1] * $v[1] + $v[2] * $v[2]);
        if ($dist >= $radius) {
            return;
        }
        $points = (int) ($damage * (1.0 - $dist / $radius));
        if ($points < 1) {
            return;
        }
        // G_RadiusDamage → G_Damage: knockback on full points first (RJ), then self ×0.5 HP
        $dir = [
            $player->origin[0] - $origin[0],
            $player->origin[1] - $origin[1],
            $player->origin[2] - $origin[2] + 24.0,
        ];
        $len = sqrt($dir[0] * $dir[0] + $dir[1] * $dir[1] + $dir[2] * $dir[2]) ?: 1.0;
        $scale = 1000.0 * $points / 200.0;
        $player->velocity[0] += ($dir[0] / $len) * $scale;
        $player->velocity[1] += ($dir[1] / $len) * $scale;
        $player->velocity[2] += ($dir[2] / $len) * $scale;
        // G_Damage: hold air-accel / no friction so knockback isn't cancelled on ground
        if ($player->pmTime === 0) {
            $t = $points * 2;
            if ($t < 50) {
                $t = 50;
            }
            if ($t > 200) {
                $t = 200;
            }
            $player->pmTime = $t;
            $player->pmFlags |= Player::PMF_TIME_KNOCKBACK;
        }
        $take = (int) ($points * 0.5);
        if ($take < 1) {
            $take = 1;
        }
        $player->applyDamage($take, $origin);
    }

    /**
     * TossClientItems / Drop_Item — launch current SG/plasma (if ammo) with TR_GRAVITY.
     * Vanishes after 30s; no map respawn.
     */
    public function tossClientItems(Player $player, CollisionModel $cm): void
    {
        $weapon = $player->weapon;
        if ($weapon === Player::WP_MACHINEGUN && $this->weaponState->state === WeaponState::DROPPING) {
            $pending = $this->weaponState->pendingWeapon;
            if ($pending > Player::WP_MACHINEGUN && ($player->weapons & (1 << $pending)) !== 0) {
                $weapon = $pending;
            }
        }
        if ($weapon <= Player::WP_MACHINEGUN || ($player->ammo[$weapon] ?? 0) <= 0) {
            return;
        }
        $classname = match ($weapon) {
            Player::WP_SHOTGUN => 'weapon_shotgun',
            Player::WP_PLASMA => 'weapon_plasmagun',
            default => null,
        };
        if ($classname === null) {
            return;
        }
        $md3 = null;
        foreach ($this->items as $it) {
            if ($it['classname'] === $classname && ($it['md3'] ?? null) !== null) {
                $md3 = $it['md3'];
                break;
            }
        }
        if ($md3 === null) {
            return;
        }
        // Drop_Item: yaw-only AngleVectors * 150, Z += 200 + crandom()*50
        $fwd = $this->forward([0.0, $player->viewangles[1], 0.0]);
        $crandom = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
        $origin = $player->origin;
        $this->items[] = [
            'classname' => $classname,
            'origin' => $origin,
            'angles' => [0.0, $player->viewangles[1], 0.0],
            'target' => '',
            'targetname' => '',
            'model' => '',
            'mins' => [$origin[0] - 15, $origin[1] - 15, $origin[2] - 15],
            'maxs' => [$origin[0] + 15, $origin[1] + 15, $origin[2] + 15],
            'active' => true,
            'respawnAt' => 0.0,
            'miscTime' => $this->time,
            'wait' => 0.0,
            'dropped' => true,
            'despawnAt' => $this->time + 30.0,
            'velocity' => [
                $fwd[0] * 150.0,
                $fwd[1] * 150.0,
                200.0 + $crandom * 50.0,
            ],
            'grounded' => false,
            'md3Path' => self::ITEM_MODELS[$classname] ?? '',
            'md3' => $md3,
            'md3Sphere' => null,
        ];
    }

    /**
     * G_RunItem / G_BounceItem — gravity + 0.50 bounce, stop when floor and vz < 40.
     */
    private function runDroppedItems(CollisionModel $cm, float $dt): void
    {
        $mins = [-15.0, -15.0, -15.0];
        $maxs = [15.0, 15.0, 15.0];
        foreach ($this->items as &$it) {
            if (empty($it['dropped']) || !$it['active'] || !empty($it['grounded'])) {
                continue;
            }
            $vel = $it['velocity'] ?? [0.0, 0.0, 0.0];
            $vel[2] -= Player::DEFAULT_GRAVITY * $dt;
            $start = $it['origin'];
            $end = [
                $start[0] + $vel[0] * $dt,
                $start[1] + $vel[1] * $dt,
                $start[2] + $vel[2] * $dt,
            ];
            $tr = $cm->trace($start, $end, $mins, $maxs);
            $origin = $tr->endPos;
            if ($tr->fraction >= 1.0) {
                $it['origin'] = $origin;
                $it['velocity'] = $vel;
                $it['mins'] = [$origin[0] - 15, $origin[1] - 15, $origin[2] - 15];
                $it['maxs'] = [$origin[0] + 15, $origin[1] + 15, $origin[2] + 15];
                continue;
            }
            $n = $tr->planeNormal ?? [0.0, 0.0, 1.0];
            $dot = $vel[0] * $n[0] + $vel[1] * $n[1] + $vel[2] * $n[2];
            $bounce = 0.50; // G_SpawnItem physicsBounce
            $vel = [
                ($vel[0] - 2.0 * $dot * $n[0]) * $bounce,
                ($vel[1] - 2.0 * $dot * $n[1]) * $bounce,
                ($vel[2] - 2.0 * $dot * $n[2]) * $bounce,
            ];
            if ($n[2] > 0.0 && $vel[2] < 40.0) {
                $origin[2] += 1.0;
                $it['origin'] = $origin;
                $it['velocity'] = [0.0, 0.0, 0.0];
                $it['grounded'] = true;
            } else {
                $origin[0] += $n[0];
                $origin[1] += $n[1];
                $origin[2] += $n[2];
                $it['origin'] = $origin;
                $it['velocity'] = $vel;
            }
            $it['mins'] = [$origin[0] - 15, $origin[1] - 15, $origin[2] - 15];
            $it['maxs'] = [$origin[0] + 15, $origin[1] + 15, $origin[2] + 15];
        }
        unset($it);
    }

    /** @param array{classname: string, dropped?: bool} $it */
    private function applyPickup(Player $player, array $it): void
    {
        $classname = $it['classname'];
        $dropped = !empty($it['dropped']);
        if (str_starts_with($classname, 'item_health')) {
            $add = match ($classname) {
                'item_health_small' => 5,
                'item_health_large' => 50,
                'item_health_mega' => 100,
                'item_health' => 25,
                default => 25,
            };
            // Pickup_Health: qty 5/100 overheal to STAT_MAX_HEALTH*2; else cap MAX_HEALTH
            $max = ($classname === 'item_health_small' || $classname === 'item_health_mega')
                ? Player::MAX_HEALTH * 2
                : Player::MAX_HEALTH;
            $player->health = min($max, $player->health + $add);
        } elseif (str_starts_with($classname, 'item_armor')) {
            // Pickup_Armor: cap STAT_MAX_HEALTH*2
            $player->armor = min(Player::MAX_HEALTH * 2, $player->armor + ($classname === 'item_armor_shard' ? 5 : 50));
        } elseif ($classname === 'weapon_shotgun') {
            $player->giveWeapon(Player::WP_SHOTGUN, 10, $dropped);
            $this->weaponState->wantWeapon($player, Player::WP_SHOTGUN);
        } elseif ($classname === 'weapon_plasmagun') {
            $player->giveWeapon(Player::WP_PLASMA, 50, $dropped);
            $this->weaponState->wantWeapon($player, Player::WP_PLASMA);
        } elseif ($classname === 'ammo_shells') {
            $player->addAmmo(Player::WP_SHOTGUN, 10);
        } elseif ($classname === 'ammo_bullets') {
            $player->addAmmo(Player::WP_MACHINEGUN, 50);
        } elseif ($classname === 'ammo_cells') {
            $player->addAmmo(Player::WP_PLASMA, 30);
        }
    }

    /** BG_CanItemBeGrabbed (bg_misc.c) — refuse health/armor/ammo when already at cap. */
    private function canItemBeGrabbed(Player $player, string $classname): bool
    {
        if (str_starts_with($classname, 'weapon_')) {
            return true;
        }
        if (str_starts_with($classname, 'ammo_')) {
            $wp = match ($classname) {
                'ammo_shells' => Player::WP_SHOTGUN,
                'ammo_bullets' => Player::WP_MACHINEGUN,
                default => Player::WP_PLASMA,
            };
            return ($player->ammo[$wp] ?? 0) < 200;
        }
        if (str_starts_with($classname, 'item_armor')) {
            return $player->armor < Player::MAX_HEALTH * 2;
        }
        if (str_starts_with($classname, 'item_health')) {
            // qty 5/100 overheal to 200; medium/large refuse at MAX_HEALTH
            if ($classname === 'item_health_small' || $classname === 'item_health_mega') {
                return $player->health < Player::MAX_HEALTH * 2;
            }
            return $player->health < Player::MAX_HEALTH;
        }
        return true;
    }

    /**
     * BG_PlayerTouchesItem — padded grab (X −50..+44, Y/Z ±36), ignores duck hull.
     *
     * @param list<float> $psOrigin @param list<float> $itemOrigin
     */
    private function playerTouchesItem(array $psOrigin, array $itemOrigin): bool
    {
        $dx = $psOrigin[0] - $itemOrigin[0];
        $dy = $psOrigin[1] - $itemOrigin[1];
        $dz = $psOrigin[2] - $itemOrigin[2];

        return !($dx > 44.0 || $dx < -50.0 || $dy > 36.0 || $dy < -36.0 || $dz > 36.0 || $dz < -36.0);
    }

    /**
     * @param list<float> $o @param list<float> $mins @param list<float> $maxs
     * @param list<float> $bmin @param list<float> $bmax
     */
    private function aabbTouch(array $o, array $mins, array $maxs, array $bmin, array $bmax): bool
    {
        return !($o[0] + $maxs[0] < $bmin[0]
            || $o[0] + $mins[0] > $bmax[0]
            || $o[1] + $maxs[1] < $bmin[1]
            || $o[1] + $mins[1] > $bmax[1]
            || $o[2] + $maxs[2] < $bmin[2]
            || $o[2] + $mins[2] > $bmax[2]);
    }

    /**
     * Fire current weapon (MG / shotgun multi-pellet / plasma projectile).
     * @return array{end?: list<float>, fraction?: float, dummyHit?: bool, kind: string}|null
     */
    public function fireWeapon(Player $player, CollisionModel $cm): ?array
    {
        if (!$this->weaponState->canFire()) {
            return null;
        }
        if (($player->pmFlags & Player::PMF_RESPAWNED) !== 0) {
            return null;
        }

        if ($player->weapon === Player::WP_SHOTGUN) {
            if (($player->ammo[Player::WP_SHOTGUN] ?? 0) <= 0) {
                return ['kind' => 'noammo'];
            }
            $player->ammo[Player::WP_SHOTGUN]--;
            $player->ammoShotgun = $player->ammo[Player::WP_SHOTGUN];
            $this->weaponState->beginFire(1.0);
            return $this->fireShotgun($player, $cm);
        }

        if ($player->weapon === Player::WP_MACHINEGUN) {
            if (($player->ammo[Player::WP_MACHINEGUN] ?? 0) <= 0) {
                return ['kind' => 'noammo'];
            }
            $player->ammo[Player::WP_MACHINEGUN]--;
            $this->weaponState->beginFire(0.1);
            return $this->fireMachinegun($player, $cm);
        }

        if ($player->weapon === Player::WP_PLASMA) {
            if (($player->ammo[Player::WP_PLASMA] ?? 0) <= 0) {
                return ['kind' => 'noammo'];
            }
            $player->ammo[Player::WP_PLASMA]--;
            $player->ammoPlasma = $player->ammo[Player::WP_PLASMA];
            $this->weaponState->beginFire(0.1);
            return $this->firePlasma($player);
        }

        return null;
    }

    /**
     * CalcMuzzlePoint (g_weapon.c) — eye + 14 along forward.
     *
     * @return list<float>
     */
    private function calcMuzzlePoint(Player $player): array
    {
        $eye = $player->eyeOrigin();
        $fwd = $this->forward($player->viewangles);

        return [
            $eye[0] + $fwd[0] * 14.0,
            $eye[1] + $fwd[1] * 14.0,
            $eye[2] + $fwd[2] * 14.0,
        ];
    }

    /** @return array{kind: string, hits: int, dummyHit: bool, end: list<float>, normal: list<float>|null, impacts: list<array{origin: list<float>, normal: list<float>}>} */
    private function fireShotgun(Player $player, CollisionModel $cm): array
    {
        $muzzle = $this->calcMuzzlePoint($player);
        $fwd = $this->forward($player->viewangles);
        $right = $this->right($player->viewangles);
        $up = $this->up($player->viewangles);
        $seed = (int) ($this->time * 1000) & 0xffff;
        $hits = 0;
        $dummyHit = false;
        $lastEnd = $muzzle;
        $lastNormal = [0.0, 0.0, 1.0];
        /** @var list<array{origin: list<float>, normal: list<float>}> $impacts */
        $impacts = [];
        for ($i = 0; $i < 11; $i++) {
            $r = $this->crandom($seed) * 700.0;
            $u = $this->crandom($seed) * 700.0;
            $dir = [
                $fwd[0] + $right[0] * $r * (1.0 / 8192.0) + $up[0] * $u * (1.0 / 8192.0),
                $fwd[1] + $right[1] * $r * (1.0 / 8192.0) + $up[1] * $u * (1.0 / 8192.0),
                $fwd[2] + $right[2] * $r * (1.0 / 8192.0) + $up[2] * $u * (1.0 / 8192.0),
            ];
            $len = sqrt($dir[0] ** 2 + $dir[1] ** 2 + $dir[2] ** 2) ?: 1.0;
            $dir = [$dir[0] / $len, $dir[1] / $len, $dir[2] / $len];
            $end = [
                $muzzle[0] + $dir[0] * 8192,
                $muzzle[1] + $dir[1] * 8192,
                $muzzle[2] + $dir[2] * 8192,
            ];
            $tr = $cm->trace($muzzle, $end, [0, 0, 0], [0, 0, 0], CollisionModel::MASK_SHOT);
            $pelletEnd = null;
            $pelletNormal = [0.0, 0.0, 1.0];
            if ($tr->fraction < 1.0) {
                // Bullet_Fire / ShotgunPellet: SURF_NOIMPACT → no mark/FX
                if (($tr->surfaceFlags & MarkFragments::SURF_NOIMPACT) === 0) {
                    $hits++;
                    $lastEnd = $tr->endPos;
                    $lastNormal = $tr->planeNormal;
                    $pelletEnd = $tr->endPos;
                    $pelletNormal = $tr->planeNormal ?? [0.0, 0.0, 1.0];
                }
            }
            foreach ($this->dummies as &$d) {
                if (!$d['alive']) {
                    continue;
                }
                $t = $this->rayAabb($muzzle, $dir, $d['mins'], $d['maxs']);
                if ($t !== null && $t < $tr->fraction * 8192) {
                    $d['health'] -= 10;
                    $dummyHit = true;
                    $lastEnd = [$muzzle[0] + $dir[0] * $t, $muzzle[1] + $dir[1] * $t, $muzzle[2] + $dir[2] * $t];
                    $lastNormal = [-$dir[0], -$dir[1], -$dir[2]];
                    $pelletEnd = $lastEnd;
                    $pelletNormal = $lastNormal;
                    if ($d['health'] <= 0) {
                        $d['alive'] = false;
                    }
                }
            }
            unset($d);
            if ($pelletEnd !== null) {
                $impacts[] = ['origin' => $pelletEnd, 'normal' => $pelletNormal];
            }
        }
        return [
            'kind' => 'shotgun',
            'hits' => $hits,
            'dummyHit' => $dummyHit,
            'end' => $lastEnd,
            'normal' => $lastNormal,
            'impacts' => $impacts,
        ];
    }

    /** @return array{kind: string, end: list<float>, fraction: float, dummyHit: bool} */
    private function fireMachinegun(Player $player, CollisionModel $cm): array
    {
        $muzzle = $this->calcMuzzlePoint($player);
        $fwd = $this->forward($player->viewangles);
        $right = $this->right($player->viewangles);
        $up = $this->up($player->viewangles);
        // Bullet_Fire: polar disk (not shotgun independent axes)
        $theta = (mt_rand() / mt_getrandmax()) * M_PI * 2.0;
        $spread = 200.0; // MACHINEGUN_SPREAD
        $r = cos($theta) * ((mt_rand() / mt_getrandmax()) * 2.0 - 1.0) * $spread;
        $u = sin($theta) * ((mt_rand() / mt_getrandmax()) * 2.0 - 1.0) * $spread;
        $dir = [
            $fwd[0] + $right[0] * $r * (1.0 / 8192.0) + $up[0] * $u * (1.0 / 8192.0),
            $fwd[1] + $right[1] * $r * (1.0 / 8192.0) + $up[1] * $u * (1.0 / 8192.0),
            $fwd[2] + $right[2] * $r * (1.0 / 8192.0) + $up[2] * $u * (1.0 / 8192.0),
        ];
        $len = sqrt($dir[0] ** 2 + $dir[1] ** 2 + $dir[2] ** 2) ?: 1.0;
        $dir = [$dir[0] / $len, $dir[1] / $len, $dir[2] / $len];
        $end = [
            $muzzle[0] + $dir[0] * 8192,
            $muzzle[1] + $dir[1] * 8192,
            $muzzle[2] + $dir[2] * 8192,
        ];
        $tr = $cm->trace($muzzle, $end, [0, 0, 0], [0, 0, 0], CollisionModel::MASK_SHOT);
        $hit = [
            'kind' => 'machinegun',
            'end' => $tr->endPos,
            'fraction' => $tr->fraction,
            'normal' => $tr->planeNormal ?? [0.0, 0.0, 1.0],
            'surfaceFlags' => $tr->surfaceFlags,
            'dummyHit' => false,
        ];
        foreach ($this->dummies as &$d) {
            if (!$d['alive']) {
                continue;
            }
            $t = $this->rayAabb($muzzle, $dir, $d['mins'], $d['maxs']);
            if ($t !== null && $t < $tr->fraction * 8192) {
                $d['health'] -= 7;
                $hit['dummyHit'] = true;
                $hit['end'] = [$muzzle[0] + $dir[0] * $t, $muzzle[1] + $dir[1] * $t, $muzzle[2] + $dir[2] * $t];
                $hit['fraction'] = $t / 8192;
                $hit['normal'] = [-$dir[0], -$dir[1], -$dir[2]];
                $hit['surfaceFlags'] = 0;
                if ($d['health'] <= 0) {
                    $d['alive'] = false;
                }
            }
        }
        unset($d);
        return $hit;
    }

    /** @return array{kind: string} */
    private function firePlasma(Player $player): array
    {
        $muzzle = $this->calcMuzzlePoint($player);
        $fwd = $this->forward($player->viewangles);
        // MISSILE_PRESTEP ~50ms at 2000 → 100 units from CalcMuzzlePoint
        $origin = [
            $muzzle[0] + $fwd[0] * 100.0,
            $muzzle[1] + $fwd[1] * 100.0,
            $muzzle[2] + $fwd[2] * 100.0,
        ];
        $this->projectiles[] = new Projectile(
            origin: $origin,
            velocity: [$fwd[0] * Projectile::SPEED, $fwd[1] * Projectile::SPEED, $fwd[2] * Projectile::SPEED],
            dieAt: $this->time + Projectile::LIFETIME,
            trailTime: $this->time,
        );
        return ['kind' => 'plasma'];
    }

    /** Q_crandom style: updates seed by ref via static-ish pattern */
    private function crandom(int &$seed): float
    {
        $seed = (214013 * $seed + 2531011) & 0x7fffffff;
        return (($seed >> 16) & 0x7fff) / 32768.0 * 2.0 - 1.0;
    }

    /**
     * @param list<float> $origin @param list<float> $dir @param list<float> $mins @param list<float> $maxs
     */
    private function rayAabb(array $origin, array $dir, array $mins, array $maxs): ?float
    {
        $tmin = 0.0;
        $tmax = 8192.0;
        for ($i = 0; $i < 3; $i++) {
            if (abs($dir[$i]) < 1e-8) {
                if ($origin[$i] < $mins[$i] || $origin[$i] > $maxs[$i]) {
                    return null;
                }
                continue;
            }
            $ood = 1.0 / $dir[$i];
            $t1 = ($mins[$i] - $origin[$i]) * $ood;
            $t2 = ($maxs[$i] - $origin[$i]) * $ood;
            if ($t1 > $t2) {
                $tmp = $t1;
                $t1 = $t2;
                $t2 = $tmp;
            }
            $tmin = max($tmin, $t1);
            $tmax = min($tmax, $t2);
            if ($tmin > $tmax) {
                return null;
            }
        }
        return $tmin >= 0 ? $tmin : null;
    }

    public function damagePlayerAt(Player $player, array $point, int $damage): void
    {
        $o = $player->origin;
        if ($point[0] >= $o[0] + Player::MINS[0] && $point[0] <= $o[0] + Player::MAXS[0]
            && $point[1] >= $o[1] + Player::MINS[1] && $point[1] <= $o[1] + Player::MAXS[1]
            && $point[2] >= $o[2] + Player::MINS[2] && $point[2] <= $o[2] + Player::MAXS[2]
        ) {
            $player->applyDamage($damage, $point);
        }
    }

    /** @param list<float> $angles @return list<float> */
    private function forward(array $angles): array
    {
        $yaw = $angles[1] * M_PI * 2 / 360;
        $pitch = $angles[0] * M_PI * 2 / 360;
        $sy = sin($yaw);
        $cy = cos($yaw);
        $sp = sin($pitch);
        $cp = cos($pitch);
        return [$cp * $cy, $cp * $sy, -$sp];
    }

    /** @param list<float> $angles @return list<float> */
    private function right(array $angles): array
    {
        $yaw = $angles[1] * M_PI * 2 / 360;
        return [-sin($yaw), cos($yaw), 0.0];
    }

    /** @param list<float> $angles @return list<float> */
    private function up(array $angles): array
    {
        $f = $this->forward($angles);
        $r = $this->right($angles);
        return [
            $r[1] * $f[2] - $r[2] * $f[1],
            $r[2] * $f[0] - $r[0] * $f[2],
            $r[0] * $f[1] - $r[1] * $f[0],
        ];
    }
}
