<?php

declare(strict_types=1);

namespace Quake3\Game;

use Quake3\Filesystem\FileSystem;
use Quake3\Render\Md3Model;

/**
 * First-person machinegun via CG_AddViewWeapon hierarchy:
 * hand (tags) → gun on tag_weapon → barrel on tag_barrel → flash on tag_flash.
 */
final class ViewWeapon
{
    public const LAND_DEFLECT_TIME = 150;
    public const LAND_RETURN_TIME = 300;

    public ?Md3Model $hand = null;
    public ?Md3Model $gun = null;
    public ?Md3Model $barrel = null;
    public ?Md3Model $flash = null;

    public float $flashUntil = 0.0;
    public int $handFrame = 0;
    public bool $bobEnabled = true;

    // pe.barrel* — CG_MachinegunSpinAngle
    private float $barrelTimeMs = 0.0;
    private float $barrelAngle = 0.0;
    private bool $barrelSpinning = false;

    // cg_gun_x/y/z defaults 0
    public float $gunX = 0.0;
    public float $gunY = 0.0;
    public float $gunZ = 0.0;

    public static function loadMachinegun(FileSystem $fs): self
    {
        return self::load($fs, 'machinegun');
    }

    /**
     * CG_RegisterWeapon model paths: models/weapons2/{name}/{name}_{hand,barrel,flash}.md3
     */
    public static function load(FileSystem $fs, string $name): self
    {
        $vw = new self();
        $base = 'models/weapons2/' . $name . '/' . $name;
        if ($fs->exists($base . '_hand.md3')) {
            $vw->hand = Md3Model::load($fs, $base . '_hand.md3');
        }
        if ($fs->exists($base . '.md3')) {
            $vw->gun = Md3Model::load($fs, $base . '.md3');
        }
        if ($fs->exists($base . '_barrel.md3')) {
            $vw->barrel = Md3Model::load($fs, $base . '_barrel.md3');
        }
        if ($fs->exists($base . '_flash.md3')) {
            $vw->flash = Md3Model::load($fs, $base . '_flash.md3');
        }
        // Fallback hand → shotgun_hand like CG_RegisterWeapon
        if ($vw->hand === null && $fs->exists('models/weapons2/shotgun/shotgun_hand.md3')) {
            $vw->hand = Md3Model::load($fs, 'models/weapons2/shotgun/shotgun_hand.md3');
        }
        return $vw;
    }

    /** @return array<int, self> WP_* => ViewWeapon */
    public static function loadKnown(FileSystem $fs): array
    {
        $map = [
            Player::WP_MACHINEGUN => 'machinegun',
            Player::WP_SHOTGUN => 'shotgun',
            Player::WP_PLASMA => 'plasma',
        ];
        $out = [];
        foreach ($map as $wp => $name) {
            $vw = self::load($fs, $name);
            if ($vw->gun !== null) {
                $out[$wp] = $vw;
            }
        }
        return $out;
    }

    public function update(Player $player, WeaponState $ws, float $dt, bool $onGround): void
    {
        // Map torso-ish frames onto hand MD3 frames (idle = 0 until anim map exists)
        $n = $this->hand?->numFrames ?? 1;
        if ($n <= 1) {
            $this->handFrame = 0;
            return;
        }
        if ($ws->state === WeaponState::FIRING) {
            $this->handFrame = min($n - 1, 1 + ((int) floor(microtime(true) * 15)) % max(1, $n - 1));
        } elseif ($ws->state === WeaponState::RAISING) {
            $this->handFrame = (int) max(0, ($n - 1) * (1.0 - $ws->weaponTime / 0.25));
        } elseif ($ws->state === WeaponState::DROPPING) {
            $this->handFrame = (int) max(0, ($n - 1) * ($ws->weaponTime / 0.20));
        } else {
            $this->handFrame = 0;
        }
    }

    public function onFire(float $now): void
    {
        $this->flashUntil = $now + 0.02; // MUZZLE_FLASH_TIME 20ms (cg_local.h)
    }

    public function flashing(float $now): bool
    {
        return $now < $this->flashUntil;
    }

    /**
     * CG_CalculateWeaponPosition (cg_weapons.c).
     *
     * @param list<float> $viewOrigin
     * @param list<float> $viewAngles pitch,yaw,roll
     * @return array{0: list<float>, 1: list<float>} origin, angles
     */
    public function calculateWeaponPosition(Player $player, array $viewOrigin, array $viewAngles, float $timeSec): array
    {
        $origin = $viewOrigin;
        $angles = [
            $viewAngles[0],
            $viewAngles[1],
            $viewAngles[2] ?? 0.0,
        ];

        if (!$this->bobEnabled) {
            return [$origin, $angles];
        }

        $xyspeed = sqrt($player->velocity[0] ** 2 + $player->velocity[1] ** 2);
        $bobcycle = ($player->bobCycle & 128) >> 7;
        $bobfracsin = abs(sin(($player->bobCycle & 127) / 127.0 * M_PI));

        $scale = ($bobcycle & 1) ? -$xyspeed : $xyspeed;
        // gun angles from bobbing
        $angles[2] += $scale * $bobfracsin * 0.005; // ROLL
        $angles[1] += $scale * $bobfracsin * 0.01;  // YAW
        $angles[0] += $xyspeed * $bobfracsin * 0.005; // PITCH

        // drop the weapon when landing (landTime is commandTime ms)
        $timeMs = $timeSec * 1000.0;
        $delta = $timeMs - $player->landTime;
        if ($player->landChange !== 0.0 && $delta >= 0.0) {
            if ($delta < self::LAND_DEFLECT_TIME) {
                $origin[2] += $player->landChange * 0.25 * $delta / self::LAND_DEFLECT_TIME;
            } elseif ($delta < self::LAND_DEFLECT_TIME + self::LAND_RETURN_TIME) {
                $origin[2] += $player->landChange * 0.25
                    * (self::LAND_DEFLECT_TIME + self::LAND_RETURN_TIME - $delta) / self::LAND_RETURN_TIME;
            }
        }

        // idle drift — cg.time * 0.001
        $scale = $xyspeed + 40.0;
        $fracsin = sin($timeSec);
        $angles[2] += $scale * $fracsin * 0.01;
        $angles[1] += $scale * $fracsin * 0.01;
        $angles[0] += $scale * $fracsin * 0.01;

        return [$origin, $angles];
    }

    /**
     * Build hand / gun / barrel / flash poses for current view.
     *
     * @param list<float> $viewOrigin
     * @param list<float> $viewAngles pitch,yaw,roll
     * @return array{
     *   hand: array{origin: list<float>, axis: list<list<float>>},
     *   gun: ?array{origin: list<float>, axis: list<list<float>>},
     *   barrel: ?array{origin: list<float>, axis: list<list<float>>},
     *   flash: ?array{origin: list<float>, axis: list<list<float>>},
     *   debug: array<string, mixed>
     * }
     */
    public function buildPoses(
        array $viewOrigin,
        array $viewAngles,
        ?Player $player = null,
        float $timeSec = 0.0,
        ?WeaponState $weaponState = null,
    ): array {
        $origin = $viewOrigin;
        $angles = $viewAngles;
        if ($player !== null) {
            [$origin, $angles] = $this->calculateWeaponPosition($player, $viewOrigin, $viewAngles, $timeSec);
        }

        // Raise/drop: pitch gun down while swapping (torso DROP/RAISE visual stand-in)
        if ($weaponState !== null) {
            $frac = 0.0;
            if ($weaponState->state === WeaponState::DROPPING && $weaponState->weaponTime > 0) {
                $frac = 1.0 - max(0.0, min(1.0, $weaponState->weaponTime / 0.20));
            } elseif ($weaponState->state === WeaponState::RAISING && $weaponState->weaponTime > 0) {
                $frac = max(0.0, min(1.0, $weaponState->weaponTime / 0.25));
            }
            if ($frac > 0.0) {
                $angles[0] += 40.0 * $frac; // pitch down
                $origin[2] -= 6.0 * $frac;
            }
        }

        $axis = QuakeMath::anglesToAxis($angles);
        // cg_gun_x/y/z along view axis[0]/[1]/[2]
        $origin[0] += $this->gunX * $axis[0][0] + $this->gunY * $axis[1][0] + $this->gunZ * $axis[2][0];
        $origin[1] += $this->gunX * $axis[0][1] + $this->gunY * $axis[1][1] + $this->gunZ * $axis[2][1];
        $origin[2] += $this->gunX * $axis[0][2] + $this->gunY * $axis[1][2] + $this->gunZ * $axis[2][2];

        $handPose = ['origin' => $origin, 'axis' => $axis];
        $gunPose = null;
        $barrelPose = null;
        $flashPose = null;

        $tagWeapon = $this->hand?->findTag('tag_weapon', $this->handFrame);
        if ($tagWeapon !== null) {
            $gunPose = QuakeMath::positionOnTag($origin, $axis, $tagWeapon);
        } elseif ($this->gun !== null) {
            $gunPose = $handPose;
        }

        if ($gunPose !== null && $this->gun !== null) {
            $tagBarrel = $this->gun->findTag('tag_barrel', 0);
            if ($tagBarrel !== null && $this->barrel !== null) {
                // CG_AddPlayerWeapon: ROLL = CG_MachinegunSpinAngle + PositionRotatedOnTag
                $firing = $weaponState !== null && $weaponState->state === WeaponState::FIRING;
                $roll = $this->machinegunSpinAngle($timeSec * 1000.0, $firing);
                $barrelAxis = QuakeMath::anglesToAxis([0.0, 0.0, $roll]);
                $barrelPose = QuakeMath::positionRotatedOnTag(
                    $gunPose['origin'],
                    $gunPose['axis'],
                    $tagBarrel,
                    $barrelAxis,
                );
            }
            $tagFlash = $this->gun->findTag('tag_flash', 0);
            if ($tagFlash !== null) {
                // CG_AddPlayerWeapon: AnglesToAxis(0,0,crandom()*10) + PositionRotatedOnTag
                $roll = ((mt_rand() / mt_getrandmax()) * 2.0 - 1.0) * 10.0;
                $flashAxis = QuakeMath::anglesToAxis([0.0, 0.0, $roll]);
                $flashPose = QuakeMath::positionRotatedOnTag(
                    $gunPose['origin'],
                    $gunPose['axis'],
                    $tagFlash,
                    $flashAxis,
                );
            }
        }

        $debug = [
            'handFrames' => $this->hand?->numFrames ?? 0,
            'handSurfaces' => $this->hand?->numSurfaces ?? 0,
            'handTags' => $this->hand?->numTags ?? 0,
            'handFrame' => $this->handFrame,
            'gunBounds' => $this->gun?->bounds(0),
            'tag_weapon' => $tagWeapon['origin'] ?? null,
            'gunOrigin' => $gunPose['origin'] ?? null,
            'flashOrigin' => $flashPose['origin'] ?? null,
            'viewOrigin' => $viewOrigin,
            'bobCycle' => $player?->bobCycle,
        ];

        return [
            'hand' => $handPose,
            'gun' => $gunPose,
            'barrel' => $barrelPose,
            'flash' => $flashPose,
            'debug' => $debug,
        ];
    }

    /**
     * CG_MachinegunSpinAngle (SPIN_SPEED 0.9, COAST_TIME 1000).
     * Edge-detects firing to latch barrelAngle / barrelTime.
     */
    private function machinegunSpinAngle(float $timeMs, bool $firing): float
    {
        $delta = $timeMs - $this->barrelTimeMs;
        if ($this->barrelSpinning) {
            $angle = $this->barrelAngle + $delta * 0.9;
        } else {
            if ($delta > 1000.0) {
                $delta = 1000.0;
            }
            $speed = 0.5 * (0.9 + (1000.0 - $delta) / 1000.0);
            $angle = $this->barrelAngle + $delta * $speed;
        }
        // pe.barrelSpinning == !(EF_FIRING) → state edge
        if ($this->barrelSpinning === !$firing) {
            $this->barrelTimeMs = $timeMs;
            $mod = fmod($angle, 360.0);
            if ($mod < 0.0) {
                $mod += 360.0;
            }
            $this->barrelAngle = $mod;
            $this->barrelSpinning = $firing;
        }
        return $angle;
    }
}
