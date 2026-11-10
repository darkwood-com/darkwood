<?php

declare(strict_types=1);

namespace Diablo\Items;

/**
 * Early-game base item definitions (partial table).
 * Extend from item tables when full dump is wired.
 * single source for early Cathedral loot (not the old data/items/*.json set).
 */
final class ItemData
{
    /**
     * @return list<array<string,mixed>>
     */
    public static function earlyDrops(): array
    {
        return [
            ['type' => 'gold', 'name' => 'Gold', 'valueMin' => 5, 'valueMax' => 40],
            ['type' => 'potion_heal', 'name' => 'Potion of Healing'],
            ['type' => 'weapon', 'name' => 'Short Sword', 'minDamage' => 2, 'maxDamage' => 6, 'equipSlot' => 'hand', 'quality' => 'normal'],
            ['type' => 'weapon', 'name' => 'Club', 'minDamage' => 1, 'maxDamage' => 5, 'equipSlot' => 'hand', 'quality' => 'normal'],
            ['type' => 'armor', 'name' => 'Rags', 'armor' => 2, 'equipSlot' => 'body', 'quality' => 'normal'],
            ['type' => 'weapon', 'name' => 'Jagged Sword', 'minDamage' => 3, 'maxDamage' => 7, 'equipSlot' => 'hand', 'quality' => 'magic'],
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public static function rollDrop(\Diablo\Engine\Random $rng, int $dlvl): array
    {
        $table = self::earlyDrops();
        $roll = $rng->generateRnd(100);
        if ($roll < 45) {
            $base = $table[0];
            return [
                'type' => 'gold',
                'name' => 'Gold',
                'value' => $rng->between((int) $base['valueMin'], (int) $base['valueMax'] + $dlvl * 5),
            ];
        }
        if ($roll < 65) {
            return ['type' => 'potion_heal', 'name' => 'Potion of Healing'];
        }
        $idx = 2 + $rng->generateRnd(count($table) - 2);
        $it = $table[$idx];
        $it['baseName'] = (string) ($it['name'] ?? 'Item');
        $it['identified'] = true;
        if (($it['quality'] ?? '') === 'normal' && $rng->generateRnd(100) < 20 + $dlvl * 5) {
            $it['quality'] = 'magic';
            $it['identified'] = false;
            $it['name'] = $it['baseName'] . ' of the Bear';
            $it['plMinDam'] = 1;
            $it['plMaxDam'] = 2;
            $it['plAC'] = 1;
        }

        return $it;
    }
}
