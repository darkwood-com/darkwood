<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * Player hull + inventory (subset of playerState_t).
 */
final class Player
{
    public const MINS = [-15.0, -15.0, -24.0];
    public const MAXS = [15.0, 15.0, 32.0];
    public const CROUCH_MAXS_Z = 16.0;
    public const DEFAULT_VIEWHEIGHT = 26;
    public const CROUCH_VIEWHEIGHT = 12;
    public const DEFAULT_SPEED = 320.0;
    public const DEFAULT_GRAVITY = 800.0;

    public const PMF_DUCKED = 1;
    public const PMF_JUMP_HELD = 2;
    public const PMF_TIME_LAND = 32;
    public const PMF_TIME_KNOCKBACK = 64;
    public const PMF_RESPAWNED = 512;
    public const PMF_ALL_TIMES = self::PMF_TIME_LAND | self::PMF_TIME_KNOCKBACK;

    public const WP_NONE = 0;
    public const WP_GAUNTLET = 1;
    public const WP_MACHINEGUN = 2;
    public const WP_SHOTGUN = 3;
    public const WP_PLASMA = 8;
    /** STAT_MAX_HEALTH (handicap 100); spawn is MAX+25, overheal decays to this */
    public const MAX_HEALTH = 100;

    /** @param list<float> $origin @param list<float> $velocity @param list<float> $viewangles pitch/yaw/roll deg */
    public function __construct(
        public array $origin = [0.0, 0.0, 0.0],
        public array $velocity = [0.0, 0.0, 0.0],
        public array $viewangles = [0.0, 0.0, 0.0],
        public array $mins = self::MINS,
        public array $maxs = self::MAXS,
        public int $viewheight = self::DEFAULT_VIEWHEIGHT,
        public float $speed = self::DEFAULT_SPEED,
        public float $gravity = self::DEFAULT_GRAVITY,
        public bool $onGround = false,
        public int $groundEntityNum = -1,
        public int $pmFlags = 0,
        public int $pmTime = 0,
        public int $commandTime = 0,
        public int $health = 125,
        public int $armor = 0,
        public int $weapon = self::WP_MACHINEGUN,
        /** weapon bitfield */
        public int $weapons = 1 << self::WP_MACHINEGUN,
        /** @var array<int, int> weapon => ammo */
        public array $ammo = [
            self::WP_MACHINEGUN => 100,
            self::WP_SHOTGUN => 0,
            self::WP_PLASMA => 0,
        ],
        public bool $hasShotgun = false,
        public bool $hasPlasma = false,
        public int $ammoShotgun = 0,
        public int $ammoPlasma = 0,
        public bool $alive = true,
        /** ps->bobCycle 0..255 for view/weapon bob */
        public int $bobCycle = 0,
        /** Client land kick (cg.landChange / landTime ms) */
        public float $landChange = 0.0,
        public float $landTime = 0.0,
        /** CG_StepOffset — stair climb view smoothing */
        public float $stepChange = 0.0,
        public float $stepTime = 0.0,
        /** CG_DuckOffset — viewheight lerp (DUCK_TIME 100ms) */
        public float $duckChange = 0.0,
        public float $duckTime = 0.0,
        /** CG_DamageFeedback — stamped by Application when damagePending */
        public float $damageTime = -999.0,
        public float $damageValue = 0.0,
        public float $damageX = 0.0,
        public float $damageY = 0.0,
        public bool $damagePending = false,
        /** gclient pain_debounce_time (seconds) — P_DamageFeedback EV_PAIN gate */
        public float $painDebounceTime = 0.0,
        /** cg.v_dmg_pitch / v_dmg_roll (degrees) */
        public float $vDmgPitch = 0.0,
        public float $vDmgRoll = 0.0,
        /** One-shot sfx flags consumed by Application after pmove */
        public bool $justJumped = false,
        public bool $justLanded = false,
        public bool $justFootstep = false,
        /** EV_FOOTSTEP_METAL when ground SURF_METALSTEPS */
        public bool $justFootstepMetal = false,
        /** Quantized EV_STEP_* height (4/8/12/16) or 0 */
        public int $justStep = 0,
        /** ClientTimerActions residual (msec accumulator stand-in in seconds) */
        public float $timeResidual = 0.0,
    ) {
    }

    /**
     * ClientTimerActions — once per second: decay health/armor above MAX_HEALTH.
     */
    public function timerActions(float $dt): void
    {
        if (!$this->alive) {
            return;
        }
        $this->timeResidual += $dt;
        while ($this->timeResidual >= 1.0) {
            $this->timeResidual -= 1.0;
            if ($this->health > self::MAX_HEALTH) {
                $this->health--;
            }
            if ($this->armor > self::MAX_HEALTH) {
                $this->armor--;
            }
        }
    }

    /** Eye position for first-person camera. @return list<float> */
    public function eyeOrigin(): array
    {
        return [
            $this->origin[0],
            $this->origin[1],
            $this->origin[2] + $this->viewheight,
        ];
    }

    public function giveWeapon(int $weapon, int $ammoQty, bool $dropped = false): void
    {
        $this->weapons |= (1 << $weapon);
        // Pickup_Weapon: dropped / team always full qty; else fill gap or +1
        if ($dropped) {
            $this->addAmmo($weapon, $ammoQty);
        } else {
            $cur = $this->ammo[$weapon] ?? 0;
            $add = $cur < $ammoQty ? ($ammoQty - $cur) : 1;
            $this->addAmmo($weapon, $add);
        }
        if ($weapon === self::WP_SHOTGUN) {
            $this->hasShotgun = true;
        }
        if ($weapon === self::WP_PLASMA) {
            $this->hasPlasma = true;
        }
        // Do not set $this->weapon — Pickup_Weapon only grants inventory;
        // wantWeapon / client autoswitch drives drop→raise + change.wav
    }

    /** Add_Ammo (g_items.c) — clamp 200. */
    public function addAmmo(int $weapon, int $count): void
    {
        $this->ammo[$weapon] = min(200, ($this->ammo[$weapon] ?? 0) + $count);
        if ($weapon === self::WP_SHOTGUN) {
            $this->ammoShotgun = $this->ammo[$weapon];
        }
        if ($weapon === self::WP_PLASMA) {
            $this->ammoPlasma = $this->ammo[$weapon];
        }
    }

    public function currentAmmo(): int
    {
        return match ($this->weapon) {
            self::WP_SHOTGUN => $this->ammo[self::WP_SHOTGUN] ?? 0,
            self::WP_PLASMA => $this->ammo[self::WP_PLASMA] ?? 0,
            default => $this->ammo[self::WP_MACHINEGUN] ?? 0,
        };
    }

    /**
     * CG_CheckAmmo (cg_playerstate.c) — 0 ok, 1 low, 2 empty.
     * Weighted seconds remaining across owned weapons; warn if total < 5000.
     */
    public function lowAmmoWarning(): int
    {
        $total = 0;
        foreach ([self::WP_MACHINEGUN, self::WP_SHOTGUN, self::WP_PLASMA] as $i) {
            if (($this->weapons & (1 << $i)) === 0) {
                continue;
            }
            $ammo = $this->ammo[$i] ?? 0;
            $total += match ($i) {
                self::WP_SHOTGUN => $ammo * 1000,
                default => $ammo * 200,
            };
            if ($total >= 5000) {
                return 0;
            }
        }

        return $total === 0 ? 2 : 1;
    }

    public function weaponName(): string
    {
        return match ($this->weapon) {
            self::WP_SHOTGUN => 'shotgun',
            self::WP_PLASMA => 'plasma',
            default => 'machinegun',
        };
    }

    /**
     * @param list<float>|null $fromPoint world damage source (splash origin); null = centered
     */
    public function applyDamage(int $damage, ?array $fromPoint = null): void
    {
        if (!$this->alive) {
            return;
        }
        $take = $damage;
        if ($this->armor > 0) {
            $save = (int) min($this->armor, (int) ceil($damage * 0.66));
            $this->armor -= $save;
            $take -= $save;
        }
        $this->health -= $take;
        // CG_DamageFeedback (cg_playerstate.c)
        $health = $this->health > 0 ? $this->health : 1;
        $scale = $health < 40 ? 1.0 : (40.0 / (float) $health);
        $kick = (float) $damage * $scale;
        if ($kick < 5.0) {
            $kick = 5.0;
        }
        if ($kick > 10.0) {
            $kick = 10.0;
        }
        if ($fromPoint === null) {
            $this->damageX = 0.0;
            $this->damageY = 0.0;
            $this->vDmgPitch = -$kick;
            $this->vDmgRoll = 0.0;
        } else {
            // dir toward damage source (post-negate Q3 feedback dir)
            $dx = $fromPoint[0] - $this->origin[0];
            $dy = $fromPoint[1] - $this->origin[1];
            $dz = $fromPoint[2] - $this->origin[2];
            $len = sqrt($dx * $dx + $dy * $dy + $dz * $dz) ?: 1.0;
            $dir = [$dx / $len, $dy / $len, $dz / $len];
            $axis = QuakeMath::anglesToAxis($this->viewangles);
            $front = $dir[0] * $axis[0][0] + $dir[1] * $axis[0][1] + $dir[2] * $axis[0][2];
            $left = $dir[0] * $axis[1][0] + $dir[1] * $axis[1][1] + $dir[2] * $axis[1][2];
            $up = $dir[0] * $axis[2][0] + $dir[1] * $axis[2][1] + $dir[2] * $axis[2][2];
            $horiz = sqrt($front * $front + $left * $left);
            if ($horiz < 0.1) {
                $horiz = 0.1;
            }
            $this->vDmgRoll = $kick * $left;
            $this->vDmgPitch = -$kick * $front;
            $frontClamp = $front <= 0.1 ? 0.1 : $front;
            $this->damageX = max(-1.0, min(1.0, -$left / $frontClamp));
            $this->damageY = max(-1.0, min(1.0, $up / $horiz));
        }
        if ($kick > 10.0) {
            $kick = 10.0;
        }
        $this->damageValue = $kick;
        $this->damagePending = true;
        if ($this->health <= 0) {
            $this->health = 0;
            $this->alive = false;
            // Keep velocity — Q3 knockback + PM_DEAD still slides (do not zero)
        }
    }

    public function respawn(array $origin, array $angles): void
    {
        $this->origin = $origin;
        $this->viewangles = $angles;
        $this->velocity = [0.0, 0.0, 0.0];
        $this->bobCycle = 0;
        $this->landChange = 0.0;
        $this->landTime = 0.0;
        $this->duckChange = 0.0;
        $this->duckTime = 0.0;
        $this->health = self::MAX_HEALTH + 25; // ClientSpawn: MAX_HEALTH+25
        $this->armor = 0;
        $this->alive = true;
        $this->timeResidual = 0.0;
        $this->pmFlags = self::PMF_RESPAWNED | self::PMF_TIME_KNOCKBACK;
        $this->pmTime = 100;
        $this->mins = self::MINS;
        $this->maxs = self::MAXS;
        $this->viewheight = self::DEFAULT_VIEWHEIGHT;
        $this->weapon = self::WP_MACHINEGUN;
        $this->weapons = 1 << self::WP_MACHINEGUN;
        $this->ammo = [
            self::WP_MACHINEGUN => 100,
            self::WP_SHOTGUN => 0,
            self::WP_PLASMA => 0,
        ];
        $this->hasShotgun = false;
        $this->hasPlasma = false;
        $this->ammoShotgun = 0;
        $this->ammoPlasma = 0;
        $this->damageTime = -999.0;
        $this->damageValue = 0.0;
        $this->damagePending = false;
        $this->painDebounceTime = 0.0;
        $this->vDmgPitch = 0.0;
        $this->vDmgRoll = 0.0;
        $this->justJumped = false;
        $this->justLanded = false;
        $this->justFootstep = false;
        $this->justFootstepMetal = false;
    }
}
