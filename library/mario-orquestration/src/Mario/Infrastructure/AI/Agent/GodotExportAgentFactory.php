<?php

namespace App\Mario\Infrastructure\AI\Agent;

use App\Mario\Domain\Level\GameplayObject;
use App\Mario\Domain\Level\Level;
use App\Mario\Domain\Level\TerrainElement;

final class GodotExportAgentFactory
{
    public function createDeterministic(): GodotExportAgent
    {
        return new DeterministicGodotExportAgent();
    }
}

interface GodotExportAgent
{
    /**
     * @return array<string, mixed>
     */
    public function export(Level $level): array;
}

final class DeterministicGodotExportAgent implements GodotExportAgent
{
    public function export(Level $level): array
    {
        return [
            'name' => $level->name(),
            'tile_size' => $level->tileSize(),
            'width' => $level->width(),
            'height' => $level->height(),
            'layers' => [
                'terrain' => array_map(
                    static fn (TerrainElement $element): array => self::terrainToArray($element),
                    $level->layers()->terrain(),
                ),
                'objects' => array_map(
                    static fn (GameplayObject $object): array => self::objectToArray($object),
                    $level->layers()->objects(),
                ),
            ],
            'metadata' => $level->metadata(),
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
