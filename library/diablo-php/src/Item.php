<?php

declare(strict_types=1);

namespace Diablo;

final class Item
{
    public int $id = 0;
    public string $type = 'misc';
    public string $name = 'Item';
    public string $itemClass = 'misc'; // weapon, armor, potion, gold, misc
    public string $equipSlot = ''; // head, body, hand, ring, amulet
    public int $minDamage = 0;
    public int $maxDamage = 0;
    public int $armor = 0;
    public int $value = 1;
    public string $quality = 'normal'; // normal, magic, unique
    public int $x = 0;
    public int $y = 0;
    public int $stack = 1;
    /** @var array<string, int> */
    public array $bonuses = [];

    public static function gold(int $amount): self
    {
        $i = new self();
        $i->type = 'gold';
        $i->name = 'Gold';
        $i->itemClass = 'gold';
        $i->value = $amount;
        $i->stack = $amount;

        return $i;
    }

    public static function potion(string $kind = 'heal'): self
    {
        $i = new self();
        $i->type = $kind === 'mana' ? 'potion_mana' : 'potion_heal';
        $i->name = $kind === 'mana' ? 'Mana Potion' : 'Health Potion';
        $i->itemClass = 'potion';
        $i->value = 50;

        return $i;
    }

    public static function weapon(string $name, int $min, int $max, string $quality = 'normal'): self
    {
        $i = new self();
        $i->type = 'weapon';
        $i->name = $name;
        $i->itemClass = 'weapon';
        $i->equipSlot = 'hand';
        $i->minDamage = $min;
        $i->maxDamage = $max;
        $i->quality = $quality;
        $i->value = ($min + $max) * 10;

        return $i;
    }

    public static function armor(string $name, int $ac, string $slot = 'body', string $quality = 'normal'): self
    {
        $i = new self();
        $i->type = 'armor';
        $i->name = $name;
        $i->itemClass = 'armor';
        $i->equipSlot = $slot;
        $i->armor = $ac;
        $i->quality = $quality;
        $i->value = $ac * 20;

        return $i;
    }

    public static function fromData(array $d): self
    {
        $i = new self();
        foreach (['type', 'name', 'itemClass', 'equipSlot', 'quality'] as $k) {
            if (isset($d[$k])) {
                $i->$k = (string) $d[$k];
            }
        }
        foreach (['minDamage', 'maxDamage', 'armor', 'value', 'stack'] as $k) {
            if (isset($d[$k])) {
                $i->$k = (int) $d[$k];
            }
        }
        if (isset($d['bonuses']) && is_array($d['bonuses'])) {
            $i->bonuses = $d['bonuses'];
        }

        return $i;
    }

    /** @return array<string, mixed> */
    public function toSave(): array
    {
        return [
            'type' => $this->type,
            'name' => $this->name,
            'itemClass' => $this->itemClass,
            'equipSlot' => $this->equipSlot,
            'minDamage' => $this->minDamage,
            'maxDamage' => $this->maxDamage,
            'armor' => $this->armor,
            'value' => $this->value,
            'quality' => $this->quality,
            'stack' => $this->stack,
            'bonuses' => $this->bonuses,
        ];
    }

    public static function fromSave(array $d): self
    {
        return self::fromData($d);
    }
}
