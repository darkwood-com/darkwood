<?php

declare(strict_types=1);

namespace Diablo;

final class Player
{
    public const MODE_STAND = 'STAND';
    public const MODE_WALK = 'WALK';
    public const MODE_ATTACK = 'ATTACK';
    public const MODE_RANGED = 'RANGED';
    public const MODE_SPELL = 'SPELL';
    public const MODE_GOT_HIT = 'GOT_HIT';
    public const MODE_DEATH = 'DEATH';

    public int $id = 1;
    public string $name = 'Hero';
    public string $class = 'Warrior';
    public int $level = 1;
    public int $experience = 0;

    public int $x;
    public int $y;
    public int $futureX;
    public int $futureY;
    /** 0=N … 7=NW */
    public int $direction = 4;
    public string $mode = self::MODE_STAND;

    public int $hp;
    public int $maxHp;
    public int $mana;
    public int $maxMana;

    public int $strength;
    public int $magic;
    public int $dexterity;
    public int $vitality;

    public int $armorClass = 0;
    public int $minDamage = 1;
    public int $maxDamage = 3;
    public int $toHit = 50;

    public int $gold = 0;

    /** @var list<array{0: int, 1: int}> */
    public array $path = [];
    public int $pathIndex = 0;

    /** Walk animation frames remaining for current step (smooth move) */
    public int $walkFrames = 0;
    public int $walkFramesTotal = 4;
    public float $renderOffsetX = 0.0;
    public float $renderOffsetY = 0.0;

    /** Attack/hit frame counters */
    public int $actionFrame = 0;
    public int $actionFrames = 8;
    public int $hitFrame = 5;
    public bool $actionHitDone = false;

    public ?int $attackTargetMonsterId = null;
    public ?array $spellTarget = null;

    public Inventory $inventory;
    public Equipment $equipment;

    /** @var list<Item> belt slots */
    public array $belt = [];

    public function __construct(int $x, int $y, string $class = 'Warrior', string $name = 'Hero')
    {
        $this->x = $x;
        $this->y = $y;
        $this->futureX = $x;
        $this->futureY = $y;
        $this->name = $name;
        $this->class = $class;
        $this->inventory = new Inventory();
        $this->equipment = new Equipment();
        $this->applyClassDefaults($class);
        $this->hp = 0;
        $this->mana = 0;
        $this->recalcStats();
        $this->hp = $this->maxHp;
        $this->mana = $this->maxMana;
        $this->belt = [Item::potion('heal'), Item::potion('heal'), null, null];
    }

    private function applyClassDefaults(string $class): void
    {
        match ($class) {
            'Rogue' => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [20, 15, 30, 20],
            'Sorcerer' => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [15, 35, 15, 20],
            default => [$this->strength, $this->magic, $this->dexterity, $this->vitality] = [30, 10, 20, 25],
        };
    }

    public function recalcStats(): void
    {
        $this->maxHp = 20 + $this->vitality * 2 + ($this->level - 1) * 4;
        $this->maxMana = 10 + $this->magic * 2 + ($this->level - 1) * 2;
        $this->minDamage = 1 + intdiv($this->strength, 10);
        $this->maxDamage = 3 + intdiv($this->strength, 5);
        $this->armorClass = intdiv($this->dexterity, 5);
        $this->toHit = 50 + intdiv($this->dexterity, 2);
        $this->equipment->applyTo($this);
        if ($this->hp > $this->maxHp) {
            $this->hp = $this->maxHp;
        }
        if ($this->mana > $this->maxMana) {
            $this->mana = $this->maxMana;
        }
    }

    public function xpToNext(): int
    {
        return $this->level * $this->level * 100;
    }

    public function addExperience(int $xp): bool
    {
        $this->experience += $xp;
        $leveled = false;
        while ($this->experience >= $this->xpToNext()) {
            $this->experience -= $this->xpToNext();
            $this->level++;
            $this->vitality += 1;
            $this->strength += 1;
            if ($this->class === 'Sorcerer') {
                $this->magic += 2;
            } elseif ($this->class === 'Rogue') {
                $this->dexterity += 2;
            } else {
                $this->strength += 1;
                $this->vitality += 1;
            }
            $this->recalcStats();
            $this->hp = $this->maxHp;
            $this->mana = $this->maxMana;
            $leveled = true;
        }

        return $leveled;
    }

    /** @param list<array{0: int, 1: int}> $path */
    public function setPath(array $path): void
    {
        $this->path = $path;
        $this->pathIndex = 0;
        if ($path !== [] && $this->mode === self::MODE_STAND) {
            $this->startWalkStep();
        }
    }

    public function clearPath(): void
    {
        $this->path = [];
        $this->pathIndex = 0;
    }

    public function hasPath(): bool
    {
        return $this->pathIndex < count($this->path);
    }

    public function isBusy(): bool
    {
        return !in_array($this->mode, [self::MODE_STAND, self::MODE_WALK], true);
    }

    public function isDead(): bool
    {
        return $this->mode === self::MODE_DEATH || $this->hp <= 0;
    }

    private function startWalkStep(): void
    {
        if (!$this->hasPath()) {
            $this->mode = self::MODE_STAND;
            $this->walkFrames = 0;
            $this->renderOffsetX = 0;
            $this->renderOffsetY = 0;

            return;
        }
        [$nx, $ny] = $this->path[$this->pathIndex];
        $this->direction = self::dirFromDelta($nx - $this->x, $ny - $this->y);
        $this->futureX = $nx;
        $this->futureY = $ny;
        $this->mode = self::MODE_WALK;
        $this->walkFrames = $this->walkFramesTotal;
        $this->pathIndex++;
    }

    public static function dirFromDelta(int $dx, int $dy): int
    {
        // 0=N,1=NE,2=E,3=SE,4=S,5=SW,6=W,7=NW
        return match (true) {
            $dx === 0 && $dy < 0 => 0,
            $dx > 0 && $dy < 0 => 1,
            $dx > 0 && $dy === 0 => 2,
            $dx > 0 && $dy > 0 => 3,
            $dx === 0 && $dy > 0 => 4,
            $dx < 0 && $dy > 0 => 5,
            $dx < 0 && $dy === 0 => 6,
            $dx < 0 && $dy < 0 => 7,
            default => 4,
        };
    }

    public function startAttack(int $monsterId): void
    {
        $this->mode = self::MODE_ATTACK;
        $this->actionFrame = 0;
        $this->actionFrames = 8;
        $this->hitFrame = 5;
        $this->actionHitDone = false;
        $this->attackTargetMonsterId = $monsterId;
        $this->clearPath();
    }

    public function startRanged(int $tx, int $ty): void
    {
        $this->mode = self::MODE_RANGED;
        $this->actionFrame = 0;
        $this->actionFrames = 8;
        $this->hitFrame = 5;
        $this->actionHitDone = false;
        $this->spellTarget = [$tx, $ty];
        $this->direction = self::dirFromDelta($tx - $this->x, $ty - $this->y);
        $this->clearPath();
    }

    public function startSpell(string $spell, ?array $target = null): void
    {
        $this->mode = self::MODE_SPELL;
        $this->actionFrame = 0;
        $this->actionFrames = 10;
        $this->hitFrame = 6;
        $this->actionHitDone = false;
        $this->spellTarget = $target;
        $this->clearPath();
        $this->inventory; // silence
        $this->_pendingSpell = $spell;
    }

    public string $_pendingSpell = '';

    public function startGotHit(): void
    {
        if ($this->mode === self::MODE_DEATH) {
            return;
        }
        $this->mode = self::MODE_GOT_HIT;
        $this->actionFrame = 0;
        $this->actionFrames = 6;
        $this->clearPath();
    }

    public function startDeath(): void
    {
        $this->mode = self::MODE_DEATH;
        $this->hp = 0;
        $this->actionFrame = 0;
        $this->actionFrames = 16;
        $this->clearPath();
    }

    /**
     * Advance one sim tick for player modes. Returns events for Game to handle.
     * @return array{hitMonster?: int, fireMissile?: array{0:int,1:int}, cast?: string, arrived?: bool, died?: bool}
     */
    public function tick(Occupancy $occ): array
    {
        $events = [];
        if ($this->mode === self::MODE_DEATH) {
            if ($this->actionFrame < $this->actionFrames) {
                $this->actionFrame++;
            }
            $events['died'] = true;

            return $events;
        }

        if ($this->mode === self::MODE_WALK) {
            $this->walkFrames--;
            $t = 1.0 - ($this->walkFrames / max(1, $this->walkFramesTotal));
            // Iso-ish screen offset toward next tile
            $ddx = $this->futureX - $this->x;
            $ddy = $this->futureY - $this->y;
            $this->renderOffsetX = ($ddx - $ddy) * (Iso::TILE_W / 2) * $t;
            $this->renderOffsetY = ($ddx + $ddy) * (Iso::TILE_H / 2) * $t;
            if ($this->walkFrames <= 0) {
                $occ->clearPlayerAt($this->x, $this->y);
                $this->x = $this->futureX;
                $this->y = $this->futureY;
                $occ->setPlayer($this->x, $this->y, $this->id);
                $this->renderOffsetX = 0;
                $this->renderOffsetY = 0;
                $events['arrived'] = true;
                if ($this->hasPath()) {
                    $this->startWalkStep();
                } else {
                    $this->mode = self::MODE_STAND;
                }
            }

            return $events;
        }

        if ($this->mode === self::MODE_ATTACK) {
            $this->actionFrame++;
            if (!$this->actionHitDone && $this->actionFrame >= $this->hitFrame) {
                $this->actionHitDone = true;
                if ($this->attackTargetMonsterId !== null) {
                    $events['hitMonster'] = $this->attackTargetMonsterId;
                }
            }
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
                $this->attackTargetMonsterId = null;
            }

            return $events;
        }

        if ($this->mode === self::MODE_RANGED) {
            $this->actionFrame++;
            if (!$this->actionHitDone && $this->actionFrame >= $this->hitFrame) {
                $this->actionHitDone = true;
                if ($this->spellTarget !== null) {
                    $events['fireMissile'] = $this->spellTarget;
                }
            }
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
                $this->spellTarget = null;
            }

            return $events;
        }

        if ($this->mode === self::MODE_SPELL) {
            $this->actionFrame++;
            if (!$this->actionHitDone && $this->actionFrame >= $this->hitFrame) {
                $this->actionHitDone = true;
                $events['cast'] = $this->_pendingSpell;
            }
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
            }

            return $events;
        }

        if ($this->mode === self::MODE_GOT_HIT) {
            $this->actionFrame++;
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
            }

            return $events;
        }

        // STAND: begin next path step if any
        if ($this->hasPath()) {
            $this->startWalkStep();
        }

        return $events;
    }

    public function useBeltSlot(int $slot): bool
    {
        if (!isset($this->belt[$slot]) || $this->belt[$slot] === null) {
            return false;
        }
        $item = $this->belt[$slot];
        if ($item->type === 'potion_heal') {
            $this->hp = min($this->maxHp, $this->hp + 30);
            $this->belt[$slot] = null;

            return true;
        }
        if ($item->type === 'potion_mana') {
            $this->mana = min($this->maxMana, $this->mana + 30);
            $this->belt[$slot] = null;

            return true;
        }

        return false;
    }

    /** @return array<string, mixed> */
    public function toSave(): array
    {
        return [
            'name' => $this->name,
            'class' => $this->class,
            'level' => $this->level,
            'experience' => $this->experience,
            'x' => $this->x,
            'y' => $this->y,
            'hp' => $this->hp,
            'mana' => $this->mana,
            'strength' => $this->strength,
            'magic' => $this->magic,
            'dexterity' => $this->dexterity,
            'vitality' => $this->vitality,
            'gold' => $this->gold,
            'inventory' => $this->inventory->toSave(),
            'equipment' => $this->equipment->toSave(),
            'belt' => array_map(static fn ($i) => $i?->toSave(), $this->belt),
        ];
    }

    public static function fromSave(array $data, int $x, int $y): self
    {
        $p = new self($x, $y, $data['class'] ?? 'Warrior', $data['name'] ?? 'Hero');
        $p->level = (int) ($data['level'] ?? 1);
        $p->experience = (int) ($data['experience'] ?? 0);
        $p->x = (int) ($data['x'] ?? $x);
        $p->y = (int) ($data['y'] ?? $y);
        $p->futureX = $p->x;
        $p->futureY = $p->y;
        $p->strength = (int) ($data['strength'] ?? $p->strength);
        $p->magic = (int) ($data['magic'] ?? $p->magic);
        $p->dexterity = (int) ($data['dexterity'] ?? $p->dexterity);
        $p->vitality = (int) ($data['vitality'] ?? $p->vitality);
        $p->gold = (int) ($data['gold'] ?? 0);
        $p->inventory = Inventory::fromSave($data['inventory'] ?? []);
        $p->equipment = Equipment::fromSave($data['equipment'] ?? []);
        $p->recalcStats();
        $p->hp = (int) ($data['hp'] ?? $p->maxHp);
        $p->mana = (int) ($data['mana'] ?? $p->maxMana);
        $p->belt = [];
        foreach ($data['belt'] ?? [] as $b) {
            $p->belt[] = $b ? Item::fromSave($b) : null;
        }
        while (count($p->belt) < 4) {
            $p->belt[] = null;
        }

        return $p;
    }
}
