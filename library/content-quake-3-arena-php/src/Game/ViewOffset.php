<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * CG_OffsetFirstPersonView subset: view bob, land drop, step smooth, damage kick.
 */
final class ViewOffset
{
    public const LAND_DEFLECT_TIME = 150.0;
    public const LAND_RETURN_TIME = 300.0;
    public const DAMAGE_DEFLECT_TIME = 100.0;
    public const DAMAGE_RETURN_TIME = 400.0;
    public const STEP_TIME = 200.0;
    public const MAX_STEP_CHANGE = 32.0;
    public const DUCK_TIME = 100.0;

    // default cvars
    public const BOB_UP = 0.005;
    public const BOB_PITCH = 0.002;
    public const BOB_ROLL = 0.002;
    public const RUN_PITCH = 0.002;
    public const RUN_ROLL = 0.005;

    /**
     * @param list<float> $eyeOrigin from Player::eyeOrigin()
     * @param list<float> $viewAngles pitch,yaw,roll deg
     * @param float $timeSec cg.time seconds (damage kick)
     * @param float|null $commandTimeMs ps->commandTime for land kick (defaults to timeSec*1000)
     * @return array{origin: list<float>, angles: list<float>}
     */
    public static function offsetFirstPerson(
        Player $player,
        array $eyeOrigin,
        array $viewAngles,
        float $timeSec,
        ?float $commandTimeMs = null,
    ): array {
        $origin = $eyeOrigin;
        $angles = [
            $viewAngles[0],
            $viewAngles[1],
            $viewAngles[2] ?? 0.0,
        ];

        if (!$player->alive) {
            $angles[2] = 40.0;
            $angles[0] = -15.0;
            return ['origin' => $origin, 'angles' => $angles];
        }

        $timeMs = $commandTimeMs ?? ($timeSec * 1000.0);

        // damage kick (cg.v_dmg_pitch / v_dmg_roll)
        if ($player->damageTime > -900.0 && $player->damageValue > 0.0) {
            $ratio = ($timeSec - $player->damageTime) * 1000.0;
            $pitchKick = $player->vDmgPitch;
            $rollKick = $player->vDmgRoll;
            if ($ratio >= 0.0 && $ratio < self::DAMAGE_DEFLECT_TIME) {
                $f = $ratio / self::DAMAGE_DEFLECT_TIME;
                $angles[0] += $f * $pitchKick;
                $angles[2] += $f * $rollKick;
            } elseif ($ratio < self::DAMAGE_DEFLECT_TIME + self::DAMAGE_RETURN_TIME) {
                $f = 1.0 - ($ratio - self::DAMAGE_DEFLECT_TIME) / self::DAMAGE_RETURN_TIME;
                if ($f > 0.0) {
                    $angles[0] += $f * $pitchKick;
                    $angles[2] += $f * $rollKick;
                }
            }
        }

        // velocity pitch/roll
        $fwd = self::angleVectors($angles);
        $right = [-$fwd[1], $fwd[0], 0.0];
        $rlen = sqrt($right[0] ** 2 + $right[1] ** 2) ?: 1.0;
        $right = [$right[0] / $rlen, $right[1] / $rlen, 0.0];
        $velDotFwd = $player->velocity[0] * $fwd[0] + $player->velocity[1] * $fwd[1] + $player->velocity[2] * $fwd[2];
        $velDotRight = $player->velocity[0] * $right[0] + $player->velocity[1] * $right[1];
        $angles[0] += $velDotFwd * self::RUN_PITCH;
        $angles[2] -= $velDotRight * self::RUN_ROLL;

        // bob angles + height
        $xyspeed = sqrt($player->velocity[0] ** 2 + $player->velocity[1] ** 2);
        $bobcycle = ($player->bobCycle & 128) >> 7;
        $bobfracsin = abs(sin(($player->bobCycle & 127) / 127.0 * M_PI));
        $speed = $xyspeed > 200.0 ? $xyspeed : 200.0;
        $delta = $bobfracsin * self::BOB_PITCH * $speed;
        if (($player->pmFlags & Player::PMF_DUCKED) !== 0) {
            $delta *= 3.0;
        }
        $angles[0] += $delta;
        $delta = $bobfracsin * self::BOB_ROLL * $speed;
        if (($player->pmFlags & Player::PMF_DUCKED) !== 0) {
            $delta *= 3.0;
        }
        if (($bobcycle & 1) !== 0) {
            $delta = -$delta;
        }
        $angles[2] += $delta;

        $bob = $bobfracsin * $xyspeed * self::BOB_UP;
        if ($bob > 6.0) {
            $bob = 6.0;
        }
        $origin[2] += $bob;

        // land height
        $delta = $timeMs - $player->landTime;
        if ($player->landChange !== 0.0 && $delta >= 0.0) {
            if ($delta < self::LAND_DEFLECT_TIME) {
                $f = $delta / self::LAND_DEFLECT_TIME;
                $origin[2] += $player->landChange * $f;
            } elseif ($delta < self::LAND_DEFLECT_TIME + self::LAND_RETURN_TIME) {
                $delta -= self::LAND_DEFLECT_TIME;
                $f = 1.0 - ($delta / self::LAND_RETURN_TIME);
                $origin[2] += $player->landChange * $f;
            }
        }

        // CG_StepOffset — smooth stair climbing (STEP_TIME 200)
        $stepDelta = $timeMs - $player->stepTime;
        if ($player->stepChange !== 0.0 && $stepDelta >= 0.0 && $stepDelta < self::STEP_TIME) {
            $origin[2] -= $player->stepChange * (self::STEP_TIME - $stepDelta) / self::STEP_TIME;
        }

        // duck viewheight lerp (DUCK_TIME 100)
        $duckDelta = $timeMs - $player->duckTime;
        if ($player->duckChange !== 0.0 && $duckDelta >= 0.0 && $duckDelta < self::DUCK_TIME) {
            $origin[2] -= $player->duckChange * (self::DUCK_TIME - $duckDelta) / self::DUCK_TIME;
        }

        return ['origin' => $origin, 'angles' => $angles];
    }

    /** @param list<float> $angles pitch,yaw,roll deg @return list<float> forward */
    private static function angleVectors(array $angles): array
    {
        $pitch = deg2rad($angles[0]);
        $yaw = deg2rad($angles[1]);
        $cp = cos($pitch);
        $sp = sin($pitch);
        $cy = cos($yaw);
        $sy = sin($yaw);
        return [$cp * $cy, $cp * $sy, -$sp];
    }
}
