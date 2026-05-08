<?php

namespace App\Mario\Domain\Level;

final readonly class Level
{
    /**
     * @param array{inspiration: string, copyright_safe: bool, generated_by: string} $metadata
     */
    public function __construct(
        private string $name,
        private int $tileSize,
        private int $width,
        private int $height,
        private LevelLayer $layers,
        private array $metadata,
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function tileSize(): int
    {
        return $this->tileSize;
    }

    public function width(): int
    {
        return $this->width;
    }

    public function height(): int
    {
        return $this->height;
    }

    public function layers(): LevelLayer
    {
        return $this->layers;
    }

    /**
     * @return array{inspiration: string, copyright_safe: bool, generated_by: string}
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
