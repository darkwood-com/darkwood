<?php

namespace App\Mario\Domain\Level;

final readonly class TerrainElement
{
    /**
     * @param array<string, scalar> $attributes
     */
    public function __construct(
        private string $type,
        private int $x,
        private int $y,
        private ?int $w = null,
        private ?int $h = null,
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

    public function w(): ?int
    {
        return $this->w;
    }

    public function h(): ?int
    {
        return $this->h;
    }

    /**
     * @return array<string, scalar>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
