<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Simplified Diablo hit/damage. PlrHitMonst / monster attack.
 */
final class Combat
{
    public static function playerHitMonster(Player $player, Monster $monster, Rng $rng): array
    {
        $hper = $player->toHit - $monster->armorClass;
        $hper = max(5, min(95, $hper));
        $roll = $rng->generateRnd(100);
        if ($roll >= $hper) {
            return ['hit' => false, 'damage' => 0, 'killed' => false];
        }
        $dam = $rng->between($player->minDamage, max($player->minDamage, $player->maxDamage));
        // Warrior crit chance
        if ($player->class === 'Warrior' && $rng->generateRnd(100) < $player->level) {
            $dam *= 2;
        }
        $monster->hp -= $dam;
        $killed = $monster->hp <= 0;
        if ($killed) {
            $monster->hp = 0;
            $monster->mode = Monster::MODE_DEATH;
            $monster->actionFrame = 0;
        } else {
            $monster->mode = Monster::MODE_GOT_HIT;
            $monster->actionFrame = 0;
            $monster->actionFrames = 4;
        }

        return ['hit' => true, 'damage' => $dam, 'killed' => $killed];
    }

    public static function monsterHitPlayer(Monster $monster, Player $player, Rng $rng): array
    {
        $hper = $monster->toHit - $player->armorClass;
        $hper = max(5, min(95, $hper));
        if ($rng->generateRnd(100) >= $hper) {
            return ['hit' => false, 'damage' => 0, 'killed' => false];
        }
        $dam = $rng->between($monster->minDamage, max($monster->minDamage, $monster->maxDamage));
        $player->hp -= $dam;
        $killed = $player->hp <= 0;
        if ($killed) {
            $player->startDeath();
        } else {
            $player->startGotHit();
        }

        return ['hit' => true, 'damage' => $dam, 'killed' => $killed];
    }
}
