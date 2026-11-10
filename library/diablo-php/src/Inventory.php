<?php

declare(strict_types=1);

namespace Diablo;

final class Inventory
{
    public const SLOTS = 40;

    /** @var list<?Item> */
    public array $slots = [];

    public function __construct()
    {
        $this->slots = array_fill(0, self::SLOTS, null);
    }

    public function add(Item $item): bool
    {
        if ($item->itemClass === 'gold') {
            return false; // handled by player gold
        }
        for ($i = 0; $i < self::SLOTS; $i++) {
            if ($this->slots[$i] === null) {
                $this->slots[$i] = $item;

                return true;
            }
        }

        return false;
    }

    public function remove(int $index): ?Item
    {
        if (!isset($this->slots[$index]) || $this->slots[$index] === null) {
            return null;
        }
        $item = $this->slots[$index];
        $this->slots[$index] = null;

        return $item;
    }

    /** @return list<Item> */
    public function all(): array
    {
        return array_values(array_filter($this->slots));
    }

    /** @return list<?array> */
    public function toSave(): array
    {
        return array_map(static fn ($i) => $i?->toSave(), $this->slots);
    }

    public static function fromSave(array $data): self
    {
        $inv = new self();
        foreach ($data as $idx => $d) {
            if ($d !== null && $idx < self::SLOTS) {
                $inv->slots[(int) $idx] = Item::fromSave($d);
            }
        }

        return $inv;
    }
}
