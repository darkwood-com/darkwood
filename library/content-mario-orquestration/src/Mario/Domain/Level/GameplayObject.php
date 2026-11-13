<?php

namespace App\Mario\Domain\Level;

final readonly class GameplayObject
{
    /**
     * @param array<string, scalar> $attributes
     */
    public function __construct(
        private string $type,
        private int $x,
        private int $y,
        private array $attributes = [],
    ) {
    }

    public function type(): string
    {
        return $this->type;
    }

    public function x(): int
    {
        return $this->x;
    }

    public function y(): int
    {
        return $this->y;
    }

    /**
     * @return array<string, scalar>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
