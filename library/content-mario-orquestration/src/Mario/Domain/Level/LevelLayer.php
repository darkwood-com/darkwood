<?php

namespace App\Mario\Domain\Level;

final class LevelLayer
{
    /**
     * @var list<TerrainElement>
     */
    private array $terrain = [];

    /**
     * @var list<GameplayObject>
     */
    private array $objects = [];

    /**
     * @param list<TerrainElement> $terrain
     */
    public function withTerrain(array $terrain): self
    {
        $clone = clone $this;
        $clone->terrain = $terrain;

        return $clone;
    }

    /**
     * @param list<GameplayObject> $objects
     */
    public function withObjects(array $objects): self
    {
        $clone = clone $this;
        $clone->objects = $objects;

        return $clone;
    }

    /**
     * @return list<TerrainElement>
     */
    public function terrain(): array
    {
        return $this->terrain;
    }

    /**
     * @return list<GameplayObject>
     */
    public function objects(): array
    {
        return $this->objects;
    }
}
