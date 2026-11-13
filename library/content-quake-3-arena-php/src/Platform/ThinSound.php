<?php

declare(strict_types=1);

namespace Quake3\Platform;

use Quake3\Filesystem\FileSystem;

/**
 * Thin sound: play baseq3 WAVs via afplay/paplay (no SDL_mixer).
 * Falls back to system beep when assets unavailable.
 */
final class ThinSound
{
    /** @var array<string, string> event => qpath */
    private const EVENT_SOUNDS = [
        'fire' => 'sound/weapons/machinegun/machgf1b.wav',
        'fire2' => 'sound/weapons/machinegun/machgf2b.wav',
        'fire3' => 'sound/weapons/machinegun/machgf3b.wav',
        'fire4' => 'sound/weapons/machinegun/machgf4b.wav',
        'fire_shotgun' => 'sound/weapons/shotgun/sshotf1b.wav',
        'fire_plasma' => 'sound/weapons/plasma/hyprbf1a.wav',
        'noammo' => 'sound/weapons/noammo.wav',
        'change' => 'sound/weapons/change.wav',
        'ric1' => 'sound/weapons/machinegun/ric1.wav',
        'ric2' => 'sound/weapons/machinegun/ric2.wav',
        'ric3' => 'sound/weapons/machinegun/ric3.wav',
        'tracer' => 'sound/weapons/machinegun/buletby1.wav',
        'plasma_hit' => 'sound/weapons/plasma/plasmx1a.wav',
        'pickup' => 'sound/misc/w_pkup.wav',
        'pickup_health_small' => 'sound/items/s_health.wav',
        'pickup_health' => 'sound/items/n_health.wav',
        'pickup_health_large' => 'sound/items/l_health.wav',
        'pickup_health_mega' => 'sound/items/m_health.wav',
        'pickup_armor_shard' => 'sound/misc/ar1_pkup.wav',
        'pickup_armor' => 'sound/misc/ar2_pkup.wav',
        'pickup_ammo' => 'sound/misc/am_pkup.wav',
        'item_respawn' => 'sound/items/respawn1.wav',
        'teleport_in' => 'sound/world/telein.wav',
        'teleport_out' => 'sound/world/teleout.wav',
        'pain' => 'sound/player/sarge/pain25_1.wav',
        'pain25' => 'sound/player/sarge/pain25_1.wav',
        'pain50' => 'sound/player/sarge/pain50_1.wav',
        'pain75' => 'sound/player/sarge/pain75_1.wav',
        'pain100' => 'sound/player/sarge/pain100_1.wav',
        'death1' => 'sound/player/sarge/death1.wav',
        'death2' => 'sound/player/sarge/death2.wav',
        'death3' => 'sound/player/sarge/death3.wav',
        'jump' => 'sound/player/sarge/jump1.wav',
        'land' => 'sound/player/land1.wav',
        'fall' => 'sound/player/sarge/fall1.wav',
        'footstep' => 'sound/player/footsteps/step1.wav',
        'footstep2' => 'sound/player/footsteps/step2.wav',
        'footstep3' => 'sound/player/footsteps/step3.wav',
        'footstep4' => 'sound/player/footsteps/step4.wav',
        'footstep_metal' => 'sound/player/footsteps/clank1.wav',
        'footstep_metal2' => 'sound/player/footsteps/clank2.wav',
        'footstep_metal3' => 'sound/player/footsteps/clank3.wav',
        'footstep_metal4' => 'sound/player/footsteps/clank4.wav',
        'splash' => 'sound/player/footsteps/splash1.wav',
        'door_start' => 'sound/movers/doors/dr1_strt.wav',
        'door_end' => 'sound/movers/doors/dr1_end.wav',
    ];

    private int $footstepSeq = 0;
    private int $metalFootstepSeq = 0;
    private int $mgFireSeq = 0;
    private float $lastPainTime = -1.0;
    private float $lastLoopUpdate = -1.0;
    /** @var array<string, array{pid: int, path: string}> */
    private array $loops = [];
    private bool $enabled;
    private ?FileSystem $fs;
    /** @var array<string, string> qpath => local temp path */
    private array $cache = [];
    private string $cacheDir;

    public function __construct(bool $enabled = true, ?FileSystem $fs = null)
    {
        $this->enabled = $enabled;
        $this->fs = $fs;
        $this->cacheDir = sys_get_temp_dir() . '/quake3-php-sfx';
    }

    public function setFileSystem(?FileSystem $fs): void
    {
        $this->fs = $fs;
    }

    /**
     * bg_misc.c itemInfo[].pickupSound — classname → event (weapons keep w_pkup).
     */
    public function playPickup(string $classname): void
    {
        $event = match ($classname) {
            'item_health_small' => 'pickup_health_small',
            'item_health' => 'pickup_health',
            'item_health_large' => 'pickup_health_large',
            'item_health_mega' => 'pickup_health_mega',
            'item_armor_shard' => 'pickup_armor_shard',
            'item_armor_combat', 'item_armor_body' => 'pickup_armor',
            'ammo_shells', 'ammo_bullets', 'ammo_grenades', 'ammo_rockets',
            'ammo_lightning', 'ammo_slugs', 'ammo_cells', 'ammo_bfg' => 'pickup_ammo',
            default => 'pickup',
        };
        $this->play($event);
    }

    /**
     * CG_PainEvent — health tiers + max ~2/s (500ms gate).
     */
    public function playPain(int $health, float $gameTime): void
    {
        if ($this->lastPainTime >= 0.0 && ($gameTime - $this->lastPainTime) < 0.5) {
            return;
        }
        $this->lastPainTime = $gameTime;
        $event = match (true) {
            $health < 25 => 'pain25',
            $health < 50 => 'pain50',
            $health < 75 => 'pain75',
            default => 'pain100',
        };
        $this->play($event);
    }

    /** EV_DEATH1..3 — random *deathN.wav */
    public function playDeath(): void
    {
        $this->play('death' . random_int(1, 3));
    }

    /**
     * Distance-gated looping target_speaker (spawnflags&1).
     * Binary on/off ~500u with 600u hysteresis — thin stand-in for trap_S_AddLoopingSound.
     * Also syncs plasma missileSound (`lasfly.wav`) while any bolt is alive.
     *
     * @param list<array{origin: list<float>, noise: string}> $speakers
     * @param list<float> $listener
     */
    public function updateLoops(array $speakers, array $listener, float $gameTime, bool $plasmaActive = false): void
    {
        if (!$this->enabled || $this->fs === null) {
            return;
        }

        // CG_Missile weaponInfo->missileSound — immediate start/stop
        if ($plasmaActive) {
            if (!isset($this->loops['plasma_fly'])) {
                $this->startLoop('plasma_fly', 'sound/weapons/plasma/lasfly.wav');
            }
        } elseif (isset($this->loops['plasma_fly'])) {
            $this->stopLoop('plasma_fly');
        }

        if ($this->lastLoopUpdate >= 0.0 && ($gameTime - $this->lastLoopUpdate) < 0.25) {
            return;
        }
        $this->lastLoopUpdate = $gameTime;

        /** @var array<string, string> */
        $wanted = [];
        foreach ($speakers as $i => $sp) {
            $o = $sp['origin'];
            $dx = $o[0] - $listener[0];
            $dy = $o[1] - $listener[1];
            $dz = $o[2] - $listener[2];
            $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            // Prefix: PHP coerces "0"/"1" keys to int, which breaks startLoop(string).
            $id = 'spk_' . $i;
            $near = $dist <= 500.0 || (isset($this->loops[$id]) && $dist <= 600.0);
            if ($near) {
                $wanted[$id] = $sp['noise'];
            }
        }
        foreach (array_keys($this->loops) as $id) {
            if ($id === 'plasma_fly') {
                continue;
            }
            if (!isset($wanted[$id])) {
                $this->stopLoop((string) $id);
            }
        }
        foreach ($wanted as $id => $noise) {
            if (!isset($this->loops[$id])) {
                $this->startLoop((string) $id, (string) $noise);
            }
        }
    }

    public function stopAllLoops(): void
    {
        foreach (array_keys($this->loops) as $id) {
            $this->stopLoop((string) $id);
        }
    }

    private function startLoop(string $id, string $qpath): void
    {
        $local = $this->materialize($qpath);
        if ($local === null) {
            return;
        }
        $inner = 'while true; do afplay -v 0.22 ' . escapeshellarg($local) . ' || exit; done';
        if (PHP_OS_FAMILY !== 'Darwin') {
            $player = is_executable('/usr/bin/paplay') || @is_executable('paplay') ? 'paplay' : null;
            if ($player === null) {
                return;
            }
            $inner = 'while true; do ' . $player . ' ' . escapeshellarg($local) . ' || exit; done';
        }
        $cmd = 'bash -c ' . escapeshellarg($inner) . ' >/dev/null 2>&1 & echo $!';
        $pid = (int) trim((string) @shell_exec($cmd));
        if ($pid > 0) {
            $this->loops[$id] = ['pid' => $pid, 'path' => $local];
        }
    }

    private function stopLoop(string $id): void
    {
        if (!isset($this->loops[$id])) {
            return;
        }
        $pid = $this->loops[$id]['pid'];
        if ($pid > 0) {
            @exec('pkill -P ' . $pid . ' 2>/dev/null');
            @exec('kill ' . $pid . ' 2>/dev/null');
        }
        unset($this->loops[$id]);
    }

    public function play(string $event): void
    {
        if (!$this->enabled) {
            return;
        }
        if ($event === 'footstep') {
            // Cycle step1..4 (CG random pick stand-in)
            $n = ($this->footstepSeq++ % 4) + 1;
            $event = $n === 1 ? 'footstep' : ('footstep' . $n);
        } elseif ($event === 'footstep_metal') {
            $n = ($this->metalFootstepSeq++ % 4) + 1;
            $event = $n === 1 ? 'footstep_metal' : ('footstep_metal' . $n);
        } elseif ($event === 'fire') {
            // machgf1b..4b rotation (cg_weapons.c flashSound[])
            $n = ($this->mgFireSeq++ % 4) + 1;
            $event = $n === 1 ? 'fire' : ('fire' . $n);
        } elseif ($event === 'ricochet') {
            $r = random_int(0, 2);
            $event = 'ric' . ($r + 1);
        }
        $qpath = self::EVENT_SOUNDS[$event] ?? null;
        if ($qpath !== null && $this->fs !== null) {
            $local = $this->materialize($qpath);
            if ($local !== null) {
                $this->playFile($local);
                return;
            }
        }
        $this->playFallback();
    }

    public function playPath(string $qpath): void
    {
        if (!$this->enabled) {
            return;
        }
        if ($this->fs !== null) {
            $local = $this->materialize($qpath);
            if ($local !== null) {
                $this->playFile($local);
                return;
            }
        }
        $this->playFallback();
    }

    private function materialize(string $qpath): ?string
    {
        if (isset($this->cache[$qpath]) && is_file($this->cache[$qpath])) {
            return $this->cache[$qpath];
        }
        if ($this->fs === null || !$this->fs->exists($qpath)) {
            return null;
        }
        try {
            $bytes = $this->fs->read($qpath);
        } catch (\Throwable) {
            return null;
        }
        if (!is_dir($this->cacheDir) && !@mkdir($this->cacheDir, 0700, true)) {
            return null;
        }
        $safe = preg_replace('/[^a-zA-Z0-9._-]+/', '_', $qpath) ?? 'sfx.wav';
        $path = $this->cacheDir . '/' . $safe;
        if (@file_put_contents($path, $bytes) === false) {
            return null;
        }
        $this->cache[$qpath] = $path;
        return $path;
    }

    private function playFile(string $path): void
    {
        $escaped = escapeshellarg($path);
        if (PHP_OS_FAMILY === 'Darwin') {
            @exec('afplay ' . $escaped . ' >/dev/null 2>&1 &');
            return;
        }
        if (is_executable('/usr/bin/paplay') || @is_executable('paplay')) {
            @exec('paplay ' . $escaped . ' >/dev/null 2>&1 &');
            return;
        }
        $this->playFallback();
    }

    private function playFallback(): void
    {
        if (PHP_OS_FAMILY === 'Darwin') {
            @exec('afplay /System/Library/Sounds/Tink.aiff >/dev/null 2>&1 &');
            return;
        }
        fwrite(STDERR, "\x07");
    }
}
