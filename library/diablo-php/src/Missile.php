<?php

declare(strict_types=1);

namespace Diablo;

final class Missile
{
    public int $id;
    public string $type; // arrow, firebolt, enemy_bolt
    public float $x;
    public float $y;
    public float $vx;
    public float $vy;
    public int $damageMin;
    public int $damageMax;
    public int $ownerId; // player id or -monster id
    public bool $fromPlayer;
    public bool $alive = true;
    public int $life = 40;

    public function __construct(
        int $id,
        string $type,
        float $x,
        float $y,
        float $tx,
        float $ty,
        int $dmin,
        int $dmax,
        int $ownerId,
        bool $fromPlayer,
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->x = $x;
        $this->y = $y;
        $dx = $tx - $x;
        $dy = $ty - $y;
        $len = sqrt($dx * $dx + $dy * $dy);
        if ($len < 0.001) {
            $len = 1.0;
        }
        $speed = $type === 'firebolt' ? 0.55 : 0.45;
        $this->vx = ($dx / $len) * $speed;
        $this->vy = ($dy / $len) * $speed;
        $this->damageMin = $dmin;
        $this->damageMax = $dmax;
        $this->ownerId = $ownerId;
        $this->fromPlayer = $fromPlayer;
    }

    public function tick(Dungeon $dungeon): void
    {
        if (!$this->alive) {
            return;
        }
        $this->x += $this->vx;
        $this->y += $this->vy;
        $this->life--;
        $gx = (int) round($this->x);
        $gy = (int) round($this->y);
        if ($this->life <= 0 || !$dungeon->inBounds($gx, $gy) || $dungeon->isWall($gx, $gy)) {
            $this->alive = false;
        }
    }

    public function tileX(): int
    {
        return (int) round($this->x);
    }

    public function tileY(): int
    {
        return (int) round($this->y);
    }
}
