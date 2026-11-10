<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * Weapon raise/drop/ready/fire timing (bg_pmove weapon state subset).
 */
final class WeaponState
{
    public const READY = 0;
    public const RAISING = 1;
    public const DROPPING = 2;
    public const FIRING = 3;

    public int $state = self::READY;
    public float $weaponTime = 0.0;
    public int $pendingWeapon = -1;
    /** EV_CHANGE_WEAPON — consumed by Application for change.wav */
    public bool $justChangedWeapon = false;
    /** CG_DrawWeaponSelect — cg.weaponSelect / weaponSelectTime (seconds) */
    public int $selectWeapon = Player::WP_MACHINEGUN;
    public float $selectTime = -999.0;

    public function showSelect(int $weapon, float $timeSec): void
    {
        $this->selectWeapon = $weapon;
        $this->selectTime = $timeSec;
    }

    /**
     * CG_NextWeapon_f / CG_PrevWeapon_f — cycle owned weapons that have ammo.
     * Always flashes the select strip (weaponSelectTime) even if nothing else is selectable.
     */
    public function cycleWeapon(Player $player, int $dir, float $timeSec): void
    {
        $sel = $this->selectWeapon;
        $original = $sel;
        $this->showSelect($sel, $timeSec);
        for ($i = 0; $i < 16; $i++) {
            $sel += $dir;
            if ($sel >= 16) {
                $sel = 0;
            }
            if ($sel < 0) {
                $sel = 15;
            }
            if ($sel === Player::WP_GAUNTLET) {
                continue;
            }
            if (!$this->weaponSelectable($player, $sel)) {
                continue;
            }
            $this->showSelect($sel, $timeSec);
            $this->wantWeapon($player, $sel);

            return;
        }
        $this->selectWeapon = $original;
    }

    /** CG_WeaponSelectable — owned and has ammo. */
    private function weaponSelectable(Player $player, int $weapon): bool
    {
        if (($player->ammo[$weapon] ?? 0) <= 0) {
            return false;
        }

        return ($player->weapons & (1 << $weapon)) !== 0;
    }

    public function wantWeapon(Player $player, int $weapon): void
    {
        if (($player->weapons & (1 << $weapon)) === 0) {
            return;
        }
        if ($weapon === $player->weapon && $this->state !== self::DROPPING) {
            return;
        }
        // Already dropping: advance pending like Q3 cmd.weapon (Finish reads latest)
        if ($this->state === self::DROPPING) {
            $this->pendingWeapon = $weapon;
            $this->selectWeapon = $weapon;
            return;
        }
        $this->pendingWeapon = $weapon;
        $this->selectWeapon = $weapon;
        // PM_Weapon: can't BeginWeaponChange while WEAPON_FIRING with weaponTime > 0
        if ($this->state === self::FIRING && $this->weaponTime > 0) {
            return;
        }
        $this->beginDrop();
    }

    /**
     * CG_OutOfAmmoChange — pick highest owned weapon that still has ammo.
     * Silent (EV_NOAMMO StartSound is commented out in cg_event.c).
     * EV_NOAMMO sets WEAPON_FIRING + weaponTime += 500 before autoswitch can drop.
     */
    public function outOfAmmoChange(Player $player, float $timeSec): void
    {
        $this->selectTime = $timeSec;
        if ($this->state !== self::DROPPING && $this->state !== self::RAISING) {
            $this->state = self::FIRING;
            $this->weaponTime += 0.5; // EV_NOAMMO weaponTime += 500
        }
        // Q3 walks i=15..1; we only have MG/SG/plasma
        foreach ([Player::WP_PLASMA, Player::WP_SHOTGUN, Player::WP_MACHINEGUN] as $i) {
            if ($i === $player->weapon) {
                continue;
            }
            if (($player->weapons & (1 << $i)) === 0) {
                continue;
            }
            if (($player->ammo[$i] ?? 0) <= 0) {
                continue;
            }
            $this->wantWeapon($player, $i);
            return;
        }
        $this->selectWeapon = $player->weapon;
    }

    /** ClientSpawn — WEAPON_READY, machinegun, no pending drop. */
    public function resetSpawn(): void
    {
        $this->state = self::READY;
        $this->weaponTime = 0.0;
        $this->pendingWeapon = -1;
        $this->justChangedWeapon = false;
        $this->selectWeapon = Player::WP_MACHINEGUN;
        $this->selectTime = -999.0;
    }

    public function update(Player $player, float $dt): void
    {
        // PM_Weapon: decrement without clamping leftover (keeps sub-frame ROF)
        if ($this->weaponTime > 0) {
            $this->weaponTime -= $dt;
        }
        if ($this->weaponTime > 0) {
            return;
        }
        // Fire lock ended — start deferred BeginWeaponChange if queued
        if ($this->state === self::FIRING) {
            if ($this->pendingWeapon >= 0 && $this->pendingWeapon !== $player->weapon) {
                $this->beginDrop();
                return;
            }
            $this->state = self::READY;
        }
        if ($this->state === self::DROPPING && $this->pendingWeapon >= 0) {
            // PM_FinishWeaponChange
            $player->weapon = $this->pendingWeapon;
            $this->pendingWeapon = -1;
            $this->state = self::RAISING;
            $this->weaponTime += 0.25;
            return;
        }
        if ($this->state === self::RAISING) {
            $this->state = self::READY;
        }
    }

    public function canFire(): bool
    {
        return $this->weaponTime <= 0.0
            && ($this->state === self::READY || $this->state === self::FIRING);
    }

    public function beginFire(float $interval): void
    {
        $this->state = self::FIRING;
        $this->weaponTime += $interval; // PM_Weapon += addTime (keep leftover)
    }

    /** PM_BeginWeaponChange — drop + EV_CHANGE_WEAPON (weaponTime += 200). */
    private function beginDrop(): void
    {
        $this->state = self::DROPPING;
        $this->weaponTime += 0.20;
        $this->justChangedWeapon = true;
    }
}
