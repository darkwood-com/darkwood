<?php

declare(strict_types=1);

namespace Diablo;

final class Towner
{
    public int $id;
    public string $name;
    public string $role; // healer, merchant, blacksmith
    public int $x;
    public int $y;

    public function __construct(int $id, string $name, string $role, int $x, int $y)
    {
        $this->id = $id;
        $this->name = $name;
        $this->role = $role;
        $this->x = $x;
        $this->y = $y;
    }
}
