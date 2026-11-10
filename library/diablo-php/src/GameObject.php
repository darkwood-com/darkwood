<?php

declare(strict_types=1);

namespace Diablo;

final class GameObject
{
    public const TYPE_DOOR = 'door';
    public const TYPE_CHEST = 'chest';
    public const TYPE_BARREL = 'barrel';
    public const TYPE_STAIRS_DOWN = 'stairs_down';
    public const TYPE_STAIRS_UP = 'stairs_up';

    public int $id;
    public string $type;
    public int $x;
    public int $y;
    public bool $opened = false;
    public bool $solid = true;
    public string $lootHint = '';

    public function __construct(int $id, string $type, int $x, int $y)
    {
        $this->id = $id;
        $this->type = $type;
        $this->x = $x;
        $this->y = $y;
        if ($type === self::TYPE_DOOR) {
            $this->solid = true;
            $this->opened = false;
        } elseif ($type === self::TYPE_STAIRS_DOWN || $type === self::TYPE_STAIRS_UP) {
            $this->solid = false;
            $this->opened = true;
        } else {
            $this->solid = false;
        }
    }

    public function operate(Dungeon $dungeon, Rng $rng): ?Item
    {
        if ($this->type === self::TYPE_DOOR) {
            if (!$this->opened) {
                $this->opened = true;
                $this->solid = false;
                $dungeon->openDoor($this->x, $this->y);
            }

            return null;
        }
        if ($this->type === self::TYPE_CHEST || $this->type === self::TYPE_BARREL) {
            if ($this->opened) {
                return null;
            }
            $this->opened = true;
            if ($this->type === self::TYPE_BARREL && $rng->generateRnd(100) < 40) {
                return null; // empty barrel
            }

            return $this->rollLoot($rng);
        }

        return null;
    }

    private function rollLoot(Rng $rng): Item
    {
        $roll = $rng->generateRnd(100);
        if ($roll < 40) {
            return Item::gold($rng->between(5, 50));
        }
        if ($roll < 65) {
            return Item::potion('heal');
        }
        if ($roll < 80) {
            return Item::potion('mana');
        }
        if ($roll < 90) {
            return Item::weapon('Short Sword', 2, 5);
        }

        return Item::armor('Leather Armor', 5);
    }
}
