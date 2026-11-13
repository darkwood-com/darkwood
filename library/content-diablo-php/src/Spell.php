<?php

declare(strict_types=1);

namespace Diablo;

final class Spell
{
    public static function cost(string $spell): int
    {
        return match ($spell) {
            'heal' => 15,
            'firebolt' => 6,
            default => 10,
        };
    }

    public static function castHeal(Player $player): bool
    {
        $cost = self::cost('heal');
        if ($player->mana < $cost) {
            return false;
        }
        $player->mana -= $cost;
        $heal = 20 + $player->magic;
        $player->hp = min($player->maxHp, $player->hp + $heal);

        return true;
    }

    /** @return array{0:int,1:int} damage range */
    public static function fireboltDamage(Player $player): array
    {
        $base = 4 + intdiv($player->magic, 4);

        return [$base, $base + 6 + intdiv($player->level, 2)];
    }
}
