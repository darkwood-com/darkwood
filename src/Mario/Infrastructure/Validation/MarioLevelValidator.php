<?php

namespace App\Mario\Infrastructure\Validation;

use App\Mario\Domain\Level\GameplayObject;
use App\Mario\Domain\Level\Level;
use App\Mario\Domain\Level\LevelValidationResult;
use App\Mario\Domain\Level\TerrainElement;

final class MarioLevelValidator
{
    /**
     * @param array<string, mixed> $payload
     */
    public function validate(Level $level, array $payload): LevelValidationResult
    {
        $errors = [];
        $errors = [...$errors, ...$this->validateSchema($payload)];
        $errors = [...$errors, ...$this->validateGameplay($level)];
        $errors = [...$errors, ...$this->validateCopyrightSafety($level)];

        return new LevelValidationResult([] === $errors, $errors, []);
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<string>
     */
    private function validateSchema(array $payload): array
    {
        $errors = [];

        $requiredRoot = ['name', 'tile_size', 'width', 'height', 'layers', 'metadata'];
        foreach ($requiredRoot as $key) {
            if (!array_key_exists($key, $payload)) {
                $errors[] = sprintf('Missing root key: %s', $key);
            }
        }

        if (!isset($payload['layers']) || !is_array($payload['layers'])) {
            $errors[] = 'layers must be an object.';

            return $errors;
        }

        if (!array_key_exists('terrain', $payload['layers']) || !is_array($payload['layers']['terrain'])) {
            $errors[] = 'layers.terrain must be an array.';
        }

        if (!array_key_exists('objects', $payload['layers']) || !is_array($payload['layers']['objects'])) {
            $errors[] = 'layers.objects must be an array.';
        }

        if (isset($payload['metadata']) && is_array($payload['metadata'])) {
            foreach (['inspiration', 'copyright_safe', 'generated_by'] as $key) {
                if (!array_key_exists($key, $payload['metadata'])) {
                    $errors[] = sprintf('metadata.%s is required.', $key);
                }
            }
        } else {
            $errors[] = 'metadata must be an object.';
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateGameplay(Level $level): array
    {
        $errors = [];
        $occupied = [];

        foreach ($level->layers()->objects() as $object) {
            $key = sprintf('%d:%d', $object->x(), $object->y());
            if (array_key_exists($key, $occupied)) {
                $errors[] = sprintf('Overlapping objects at (%d,%d).', $object->x(), $object->y());
            }
            $occupied[$key] = true;
        }

        foreach ($level->layers()->objects() as $object) {
            if ('enemy' !== $object->type()) {
                continue;
            }

            if (!$this->hasGroundUnderObject($object, $level->layers()->terrain())) {
                $errors[] = sprintf('Enemy at (%d,%d) has no supporting terrain.', $object->x(), $object->y());
            }
        }

        foreach ($level->layers()->terrain() as $terrain) {
            if ('gap' !== $terrain->type()) {
                continue;
            }

            if (null === $terrain->w() || $terrain->w() > 4) {
                $errors[] = sprintf('Gap at x=%d is impossible to jump safely.', $terrain->x());
            }
        }

        return $errors;
    }

    /**
     * @return list<string>
     */
    private function validateCopyrightSafety(Level $level): array
    {
        $errors = [];

        if ('world_1_1_inspired' !== $level->name()) {
            $errors[] = 'Level name must indicate inspired content only.';
        }

        if (224 !== $level->width() || 15 !== $level->height()) {
            $errors[] = 'Unexpected level dimensions for this deterministic slice.';
        }

        if ($this->looksLikeExactClone($level->layers()->terrain())) {
            $errors[] = 'Terrain pattern appears too close to an exact World 1-1 clone.';
        }

        return $errors;
    }

    /**
     * @param list<TerrainElement> $terrain
     */
    private function looksLikeExactClone(array $terrain): bool
    {
        $pipeCount = 0;
        $gapCount = 0;
        foreach ($terrain as $element) {
            if ('pipe' === $element->type()) {
                ++$pipeCount;
            }
            if ('gap' === $element->type()) {
                ++$gapCount;
            }
        }

        return 3 === $pipeCount && 1 === $gapCount;
    }

    /**
     * @param list<TerrainElement> $terrain
     */
    private function hasGroundUnderObject(GameplayObject $object, array $terrain): bool
    {
        $targetY = $object->y() + 1;

        foreach ($terrain as $element) {
            if (!in_array($element->type(), ['ground', 'platform', 'stairs'], true)) {
                continue;
            }

            $width = $element->w() ?? 1;
            $height = $element->h() ?? 1;
            $withinX = $object->x() >= $element->x() && $object->x() < ($element->x() + $width);
            $withinY = $targetY >= $element->y() && $targetY < ($element->y() + $height);

            if ($withinX && $withinY) {
                return true;
            }
        }

        return false;
    }
}
