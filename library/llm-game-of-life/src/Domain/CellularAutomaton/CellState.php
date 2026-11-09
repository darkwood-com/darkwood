<?php

declare(strict_types=1);

namespace App\Domain\CellularAutomaton;

enum CellState: int
{
    case Dead = 0;
    case Alive = 1;

    public static function fromBool(bool $alive): self
    {
        return $alive ? self::Alive : self::Dead;
    }

    public function isAlive(): bool
    {
        return self::Alive === $this;
    }
}
