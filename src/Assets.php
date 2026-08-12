<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Loads JSON data packs from data/.
 */
final class Assets
{
    private string $root;
    /** @var array<string, array> */
    private array $monsters = [];
    /** @var array<string, array> */
    private array $items = [];
    /** @var array<string, array> */
    private array $spells = [];

    public function __construct(?string $root = null)
    {
        $this->root = $root ?? dirname(__DIR__) . '/data';
        $this->loadAll();
    }

    private function loadAll(): void
    {
        $this->monsters = $this->loadDir('monsters');
        $this->items = $this->loadDir('items');
        $this->spells = $this->loadDir('spells');
    }

    /** @return array<string, array> */
    private function loadDir(string $sub): array
    {
        $dir = $this->root . '/' . $sub;
        $out = [];
        if (!is_dir($dir)) {
            return $out;
        }
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $data = json_decode((string) file_get_contents($file), true);
            if (!is_array($data)) {
                continue;
            }
            $key = (string) ($data['id'] ?? basename($file, '.json'));
            $out[$key] = $data;
        }

        return $out;
    }

    public function monster(string $id): array
    {
        return $this->monsters[$id] ?? [
            'id' => $id,
            'name' => ucfirst($id),
            'hp' => 30,
            'armorClass' => 5,
            'minDamage' => 1,
            'maxDamage' => 4,
            'toHit' => 40,
            'xpReward' => 20,
            'ai' => 'melee',
        ];
    }

    public function item(string $id): ?Item
    {
        if (!isset($this->items[$id])) {
            return match ($id) {
                'gold' => Item::gold(25),
                'potion_heal' => Item::potion('heal'),
                'potion_mana' => Item::potion('mana'),
                'short_sword' => Item::weapon('Short Sword', 2, 6),
                'leather_armor' => Item::armor('Leather Armor', 5),
                default => null,
            };
        }

        return Item::fromData($this->items[$id]);
    }

    public function spell(string $id): array
    {
        return $this->spells[$id] ?? ['id' => $id, 'mana' => Spell::cost($id)];
    }
}
