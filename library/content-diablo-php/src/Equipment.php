<?php

declare(strict_types=1);

namespace Diablo;

final class Equipment
{
    public ?Item $head = null;
    public ?Item $body = null;
    public ?Item $hand = null;
    public ?Item $ring = null;
    public ?Item $amulet = null;

    public function equip(Item $item): ?Item
    {
        $slot = $item->equipSlot;
        if ($slot === '') {
            return null;
        }
        $prev = $this->$slot ?? null;
        $this->$slot = $item;

        return $prev;
    }

    public function unequip(string $slot): ?Item
    {
        if (!property_exists($this, $slot)) {
            return null;
        }
        $item = $this->$slot;
        $this->$slot = null;

        return $item;
    }

    public function applyTo(Player $player): void
    {
        foreach ([$this->head, $this->body, $this->hand, $this->ring, $this->amulet] as $item) {
            if ($item === null) {
                continue;
            }
            $player->armorClass += $item->armor;
            if ($item->itemClass === 'weapon') {
                $player->minDamage += $item->minDamage;
                $player->maxDamage += $item->maxDamage;
            }
            foreach ($item->bonuses as $k => $v) {
                if (property_exists($player, $k)) {
                    $player->$k += (int) $v;
                }
            }
        }
    }

    /** @return array<string, ?array> */
    public function toSave(): array
    {
        return [
            'head' => $this->head?->toSave(),
            'body' => $this->body?->toSave(),
            'hand' => $this->hand?->toSave(),
            'ring' => $this->ring?->toSave(),
            'amulet' => $this->amulet?->toSave(),
        ];
    }

    public static function fromSave(array $data): self
    {
        $e = new self();
        foreach (['head', 'body', 'hand', 'ring', 'amulet'] as $slot) {
            if (!empty($data[$slot])) {
                $e->$slot = Item::fromSave($data[$slot]);
            }
        }

        return $e;
    }
}
