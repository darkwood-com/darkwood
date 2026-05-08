<?php

namespace App\Mario\Domain\Level;

final readonly class GeneratedLevel
{
    /**
     * @param list<TerrainElement> $terrain
     * @param list<GameplayObject> $objects
     * @param array{generated_by: string, copyright_safe: bool, inspiration: string} $metadata
     */
    public function __construct(
        private string $name,
        private int $tileSize,
        private int $width,
        private int $height,
        private int $playerStartX,
        private int $playerStartY,
        private int $goalX,
        private int $goalY,
        private array $terrain,
        private array $objects,
        private array $metadata,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'tile_size' => $this->tileSize,
            'width' => $this->width,
            'height' => $this->height,
            'player_start' => ['x' => $this->playerStartX, 'y' => $this->playerStartY],
            'goal' => ['x' => $this->goalX, 'y' => $this->goalY],
            'terrain' => array_map(
                static fn (TerrainElement $element): array => self::terrainToArray($element),
                $this->terrain
            ),
            'objects' => array_map(
                static fn (GameplayObject $object): array => self::objectToArray($object),
                $this->objects
            ),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * @return array<string, scalar>
     */
    private static function terrainToArray(TerrainElement $element): array
    {
        $row = [
            'type' => $element->type(),
            'x' => $element->x(),
            'y' => $element->y(),
        ];

        if (null !== $element->w()) {
            $row['w'] = $element->w();
        }

        if (null !== $element->h()) {
            $row['h'] = $element->h();
        }

        foreach ($element->attributes() as $key => $value) {
            $row[$key] = $value;
        }

        return $row;
    }

    /**
     * @return array<string, scalar>
     */
    private static function objectToArray(GameplayObject $object): array
    {
        $row = [
            'type' => $object->type(),
            'x' => $object->x(),
            'y' => $object->y(),
        ];

        foreach ($object->attributes() as $key => $value) {
            $row[$key] = $value;
        }

        return $row;
    }
}
