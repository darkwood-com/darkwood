<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Spatial occupancy grids (dPlayer/dMonster/dItem/dObject).
 * Player ids: +(id) standing, -(id) moving into tile (occupyTile isMoving).
 */
final class Occupancy
{
    /** @var array<string, int> "x,y" => signed entity id */
    private array $players = [];
    private array $monsters = [];
    private array $items = [];
    private array $objects = [];

    public function clear(): void
    {
        $this->players = [];
        $this->monsters = [];
        $this->items = [];
        $this->objects = [];
    }

    private function key(int $x, int $y): string
    {
        return $x . ',' . $y;
    }

 /** Player::occupyTile — isMoving → negative id */
    public function occupyPlayer(int $x, int $y, int $id, bool $isMoving): void
    {
        $this->players[$this->key($x, $y)] = $isMoving ? -$id : $id;
    }

    public function setPlayer(int $x, int $y, int $id): void
    {
        $this->players[$this->key($x, $y)] = $id;
    }

    public function clearPlayerAt(int $x, int $y): void
    {
        unset($this->players[$this->key($x, $y)]);
    }

    public function clearPlayerId(int $id): void
    {
        foreach ($this->players as $k => $v) {
            if (abs($v) === abs($id)) {
                unset($this->players[$k]);
            }
        }
    }

    /** @return array<string,int> "x,y" => signed id */
    public function allPlayers(): array
    {
        return $this->players;
    }

    public function playerAt(int $x, int $y): int
    {
        return $this->players[$this->key($x, $y)] ?? 0;
    }

    public function standingPlayerAt(int $x, int $y): int
    {
        $p = $this->playerAt($x, $y);

        return $p > 0 ? $p : 0;
    }

    public function setMonster(int $x, int $y, int $id): void
    {
        $this->monsters[$this->key($x, $y)] = $id;
    }

 /** monster occupyTile — isMoving → negative id (dMonster) */
    public function occupyMonster(int $x, int $y, int $id, bool $isMoving): void
    {
        $this->monsters[$this->key($x, $y)] = $isMoving ? -$id : $id;
    }

    public function clearMonsterAt(int $x, int $y): void
    {
        unset($this->monsters[$this->key($x, $y)]);
    }

    public function clearMonsterId(int $id): void
    {
        foreach ($this->monsters as $k => $v) {
            if (abs($v) === abs($id)) {
                unset($this->monsters[$k]);
            }
        }
    }

    public function monsterAt(int $x, int $y): int
    {
        return $this->monsters[$this->key($x, $y)] ?? 0;
    }

    public function standingMonsterAt(int $x, int $y): int
    {
        $m = $this->monsterAt($x, $y);

        return $m > 0 ? $m : 0;
    }

    public function setItem(int $x, int $y, int $id): void
    {
        $this->items[$this->key($x, $y)] = $id;
    }

    public function clearItemAt(int $x, int $y): void
    {
        unset($this->items[$this->key($x, $y)]);
    }

    public function itemAt(int $x, int $y): int
    {
        return $this->items[$this->key($x, $y)] ?? 0;
    }

    public function setObject(int $x, int $y, int $id): void
    {
        $this->objects[$this->key($x, $y)] = $id;
    }

    public function clearObjectAt(int $x, int $y): void
    {
        unset($this->objects[$this->key($x, $y)]);
    }

    public function objectAt(int $x, int $y): int
    {
        return $this->objects[$this->key($x, $y)] ?? 0;
    }

    public function blocksActor(int $x, int $y, int $ignorePlayerId = 0, int $ignoreMonsterId = 0): bool
    {
        $p = $this->playerAt($x, $y);
        if ($p !== 0 && abs($p) !== abs($ignorePlayerId)) {
            return true;
        }
        // PosOkPlayer / IsTileAvailable: any non-zero dMonster blocks
        $m = $this->monsterAt($x, $y);
        if ($m !== 0 && abs($m) !== abs($ignoreMonsterId)) {
            return true;
        }

        return false;
    }
}
