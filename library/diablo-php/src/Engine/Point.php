<?php
declare(strict_types=1);
namespace Diablo\Engine;
/** Point analogue. */
final class Point
{
    public function __construct(public int $x = 0, public int $y = 0) {}
    public function equals(Point $o): bool { return $this->x === $o->x && $this->y === $o->y; }
    public function add(int $dx, int $dy): self { return new self($this->x + $dx, $this->y + $dy); }
}
