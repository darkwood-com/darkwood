<?php

declare(strict_types=1);

namespace Diablo;

final class Monster
{
    public const MODE_STAND = 'STAND';
    public const MODE_WALK = 'WALK';
    public const MODE_ATTACK = 'ATTACK';
    public const MODE_RANGED = 'RANGED';
    public const MODE_GOT_HIT = 'GOT_HIT';
    public const MODE_DEATH = 'DEATH';

    public int $id;
    public string $type = 'zombie';
    public string $name = 'Zombie';
    public string $ai = 'melee'; // melee, ranged, brute

    public int $x;
    public int $y;
    public int $futureX;
    public int $futureY;
    public int $direction = 4;
    public string $mode = self::MODE_STAND;

    public int $hp;
    public int $maxHp;
    public int $armorClass = 5;
    public int $minDamage = 1;
    public int $maxDamage = 4;
    public int $toHit = 40;
    public int $xpReward = 20;
    public int $detectionRange = 10;

    public int $walkFrames = 0;
    public int $walkFramesTotal = 5;
    public float $renderOffsetX = 0.0;
    public float $renderOffsetY = 0.0;

    public int $actionFrame = 0;
    public int $actionFrames = 8;
    public int $hitFrame = 5;
    public bool $actionHitDone = false;

    public int $aiSeed = 0;
    public bool $alive = true;
    public bool $isQuestTarget = false;

    /** @var list<array{0:int,1:int}> */
    public array $path = [];
    public int $pathIndex = 0;

    public function __construct(int $id, int $x, int $y, array $data = [])
    {
        $this->id = $id;
        $this->x = $x;
        $this->y = $y;
        $this->futureX = $x;
        $this->futureY = $y;
        foreach (['type', 'name', 'ai'] as $k) {
            if (isset($data[$k])) {
                $this->$k = (string) $data[$k];
            }
        }
        foreach (['hp', 'maxHp', 'armorClass', 'minDamage', 'maxDamage', 'toHit', 'xpReward', 'detectionRange'] as $k) {
            if (isset($data[$k])) {
                $this->$k = (int) $data[$k];
            }
        }
        if (!isset($data['maxHp'])) {
            $this->maxHp = $this->hp > 0 ? $this->hp : 30;
        }
        if (!isset($data['hp'])) {
            $this->hp = $this->maxHp;
        }
        if ($this->hp <= 0) {
            $this->hp = $this->maxHp = 30;
        }
    }

    public function distanceTo(int $px, int $py): int
    {
        return max(abs($this->x - $px), abs($this->y - $py));
    }

    public function isAdjacent(int $px, int $py): bool
    {
        return abs($this->x - $px) <= 1 && abs($this->y - $py) <= 1
            && !($this->x === $px && $this->y === $py);
    }

    /**
     * @return array{hitPlayer?: bool, fireMissile?: array{0:int,1:int}, arrived?: bool}
     */
    public function tick(Player $player, Occupancy $occ, Pathfinder $pf, Dungeon $dungeon, Rng $rng): array
    {
        $events = [];
        if (!$this->alive || $this->mode === self::MODE_DEATH) {
            if ($this->mode === self::MODE_DEATH) {
                $this->actionFrame++;
                if ($this->actionFrame > 12) {
                    $this->alive = false;
                }
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

        if ($this->mode === self::MODE_WALK) {
            $this->walkFrames--;
            $t = 1.0 - ($this->walkFrames / max(1, $this->walkFramesTotal));
            $ddx = $this->futureX - $this->x;
            $ddy = $this->futureY - $this->y;
            $this->renderOffsetX = ($ddx - $ddy) * (Iso::TILE_W / 2) * $t;
            $this->renderOffsetY = ($ddx + $ddy) * (Iso::TILE_H / 2) * $t;
            if ($this->walkFrames <= 0) {
                $occ->clearMonsterAt($this->x, $this->y);
                $this->x = $this->futureX;
                $this->y = $this->futureY;
                $occ->setMonster($this->x, $this->y, $this->id);
                $this->renderOffsetX = 0;
                $this->renderOffsetY = 0;
                $this->mode = self::MODE_STAND;
                $events['arrived'] = true;
            }

            return $events;
        }

        if ($this->mode === self::MODE_ATTACK) {
            $this->actionFrame++;
            if (!$this->actionHitDone && $this->actionFrame >= $this->hitFrame) {
                $this->actionHitDone = true;
                if ($this->isAdjacent($player->x, $player->y) && !$player->isDead()) {
                    $events['hitPlayer'] = true;
                }
            }
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
            }

            return $events;
        }

        if ($this->mode === self::MODE_RANGED) {
            $this->actionFrame++;
            if (!$this->actionHitDone && $this->actionFrame >= $this->hitFrame) {
                $this->actionHitDone = true;
                $events['fireMissile'] = [$player->x, $player->y];
            }
            if ($this->actionFrame >= $this->actionFrames) {
                $this->mode = self::MODE_STAND;
            }

            return $events;
        }

        // STAND AI
        $dist = $this->distanceTo($player->x, $player->y);
        if ($dist > $this->detectionRange || $player->isDead()) {
            return $events;
        }

        if ($this->ai === 'ranged') {
            if ($dist <= 6 && $dist >= 2) {
                $this->mode = self::MODE_RANGED;
                $this->actionFrame = 0;
                $this->actionFrames = 10;
                $this->hitFrame = 6;
                $this->actionHitDone = false;
                $this->direction = Player::dirFromDelta($player->x - $this->x, $player->y - $this->y);

                return $events;
            }
        }

        if ($this->isAdjacent($player->x, $player->y)) {
            $this->mode = self::MODE_ATTACK;
            $this->actionFrame = 0;
            $this->actionFrames = $this->ai === 'brute' ? 10 : 8;
            $this->hitFrame = $this->ai === 'brute' ? 6 : 5;
            $this->actionHitDone = false;
            $this->direction = Player::dirFromDelta($player->x - $this->x, $player->y - $this->y);

            return $events;
        }

        // Path one step toward player
        $selfId = $this->id;
        $blocked = static function (int $x, int $y) use ($occ, $player, $selfId): bool {
            if ($x === $player->x && $y === $player->y) {
                return true;
            }

            return $occ->blocksActor($x, $y, $player->id, $selfId);
        };
        $path = $pf->find($dungeon, $this->x, $this->y, $player->x, $player->y, $blocked, true);
        if ($path === []) {
            return $events;
        }
        // Don't step onto player tile
        [$nx, $ny] = $path[0];
        if ($nx === $player->x && $ny === $player->y) {
            return $events;
        }
        if ($occ->blocksActor($nx, $ny, $player->id, $this->id) || !$dungeon->isWalkable($nx, $ny)) {
            return $events;
        }
        $this->direction = Player::dirFromDelta($nx - $this->x, $ny - $this->y);
        $this->futureX = $nx;
        $this->futureY = $ny;
        $this->mode = self::MODE_WALK;
        $this->walkFrames = $this->walkFramesTotal;
        // Reserve future tile
        $occ->setMonster($nx, $ny, $this->id);

        return $events;
    }
}
