<?php

declare(strict_types=1);

namespace App\Domain\Validation;

/**
 * Validates JSON design specifications: required shape, then spatial coherence for architectural types.
 *
 * Architectural specs use hierarchical sections (foundation → structure → roof → openings).
 * Legacy `type: "generic"` keeps a flat `parts` array for non-building objects.
 */
final class DesignSpecValidator
{
    private const EPS = 0.02;

    /** @var list<string> */
    private const ARCHITECTURAL_TYPES = ['house', 'shed'];

    /**
     * @return array<string, mixed> Decoded and validated root array
     */
    public function validate(string $json): array
    {
        if ('' === $json) {
            throw new \RuntimeException('Design agent returned an empty response.');
        }

        $data = json_decode($json, associative: true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(sprintf(
                'Design agent returned invalid JSON: %s',
                json_last_error_msg(),
            ));
        }

        if (!\is_array($data)) {
            throw new \RuntimeException('Design spec is not a JSON object.');
        }

        $type = $data['type'] ?? null;
        if (!\is_string($type) || '' === $type) {
            throw new \RuntimeException('Design spec is missing required string "type" (e.g. "house" or "generic").');
        }

        if ('generic' === $type) {
            if (empty($data['parts']) || !\is_array($data['parts'])) {
                throw new \RuntimeException('Design spec with type "generic" is missing required "parts" array.');
            }
            $this->validatePartsList($data['parts']);

            return $data;
        }

        if (\in_array($type, self::ARCHITECTURAL_TYPES, true)) {
            $this->validateArchitecturalRoot($data);
            $this->validateArchitecturalSpatial($data);

            return $data;
        }

        throw new \RuntimeException(sprintf(
            'Unsupported design type "%s". Use one of: %s, or "generic".',
            $type,
            implode(', ', $this->architecturalTypesWithQuotes()),
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateArchitecturalRoot(array $data): void
    {
        foreach (['name', 'unit', 'foundation', 'structure', 'roof', 'openings', 'constraints'] as $key) {
            if (!\array_key_exists($key, $data)) {
                throw new \RuntimeException(sprintf('Architectural design spec is missing required key "%s".', $key));
            }
        }

        if (!\is_string($data['name']) || '' === $data['name']) {
            throw new \RuntimeException('Architectural design spec requires non-empty string "name".');
        }

        if (!\is_string($data['unit']) || 'm' !== $data['unit']) {
            throw new \RuntimeException('Architectural design spec requires "unit": "m" (metres).');
        }

        if (!\is_array($data['foundation'])) {
            throw new \RuntimeException('"foundation" must be an object.');
        }

        if (!\is_array($data['structure'])) {
            throw new \RuntimeException('"structure" must be an object.');
        }

        foreach (['walls', 'floor'] as $section) {
            if (empty($data['structure'][$section]) || !\is_array($data['structure'][$section])) {
                throw new \RuntimeException(sprintf('"structure.%s" must be a non-empty object.', $section));
            }
        }

        foreach (['roof', 'openings', 'constraints'] as $key) {
            if (!\is_array($data[$key])) {
                throw new \RuntimeException(sprintf('"%s" must be an object.', $key));
            }
        }

        $this->validateGeometryBlock('foundation', $data['foundation']);
        $this->validateGeometryBlock('structure.walls', $data['structure']['walls']);
        $this->validateGeometryBlock('structure.floor', $data['structure']['floor']);
        $this->validateGeometryBlock('roof', $data['roof']);

        $this->validateOpenings($data['openings']);

        foreach (['symmetry', 'alignment', 'proportions'] as $ckey) {
            if (!\array_key_exists($ckey, $data['constraints'])) {
                throw new \RuntimeException(sprintf('"constraints" must include "%s".', $ckey));
            }
        }
    }

    /**
     * @param array<string, mixed> $openings
     */
    private function validateOpenings(array $openings): void
    {
        if (!\array_key_exists('door', $openings)) {
            throw new \RuntimeException('"openings" must include a "door" object (use null geometry if no door).');
        }

        if (!\array_key_exists('windows', $openings) || !\is_array($openings['windows'])) {
            throw new \RuntimeException('"openings.windows" must be an array.');
        }

        if (null !== $openings['door'] && !\is_array($openings['door'])) {
            throw new \RuntimeException('"openings.door" must be an object or null.');
        }

        if (null !== $openings['door']) {
            $this->validateGeometryBlock('openings.door', $openings['door']);
        }

        foreach ($openings['windows'] as $i => $win) {
            if (!\is_array($win)) {
                throw new \RuntimeException(sprintf('"openings.windows[%d]" must be an object.', $i));
            }
            $this->validateGeometryBlock(sprintf('openings.windows[%d]', $i), $win);
        }
    }

    /**
     * @param array<string, mixed> $block
     */
    private function validateGeometryBlock(string $path, array $block): void
    {
        if (empty($block['geometry']) || !\is_array($block['geometry'])) {
            throw new \RuntimeException(sprintf(
                '%s must contain a "geometry" object with "type" and dimensions.',
                $path,
            ));
        }

        $geom = $block['geometry'];
        $geomType = $geom['type'] ?? null;

        if (!\is_string($geomType) || '' === $geomType) {
            throw new \RuntimeException(sprintf('%s.geometry is missing string "type".', $path));
        }

        $this->validatePrimitiveFields($path . '.geometry', $geomType, $geom);
    }

    /**
     * @param array<string, mixed> $geom
     */
    private function validatePrimitiveFields(string $path, string $geomType, array $geom): void
    {
        foreach (['x', 'y', 'z'] as $axis) {
            if (!\array_key_exists($axis, $geom) || !is_numeric($geom[$axis])) {
                throw new \RuntimeException(sprintf('%s must include numeric "%s" (world position).', $path, $axis));
            }
        }

        if ('box' === $geomType) {
            foreach (['width', 'depth', 'height'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type box) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        if ('wall_perimeter' === $geomType) {
            foreach (['outer_width', 'outer_depth', 'height', 'thickness'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type wall_perimeter) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        if ('gable_roof' === $geomType) {
            foreach (['width', 'depth', 'ridge_height'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type gable_roof) requires positive numeric "%s".', $path, $f));
                }
            }

            $axis = $geom['ridge_axis'] ?? null;
            if (!\is_string($axis) || !\in_array($axis, ['x', 'y'], true)) {
                throw new \RuntimeException(sprintf('%s (type gable_roof) requires "ridge_axis": "x" or "y".', $path));
            }

            return;
        }

        if ('cylinder' === $geomType) {
            foreach (['radius', 'height'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type cylinder) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        if ('ngon_prism' === $geomType) {
            foreach (['radius', 'height', 'sides'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type ngon_prism) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        if ('cone' === $geomType) {
            foreach (['radius', 'height'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type cone) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        if ('wedge' === $geomType) {
            foreach (['width', 'depth', 'height'] as $f) {
                if (!isset($geom[$f]) || !is_numeric($geom[$f]) || (float) $geom[$f] <= 0) {
                    throw new \RuntimeException(sprintf('%s (type wedge) requires positive numeric "%s".', $path, $f));
                }
            }

            return;
        }

        throw new \RuntimeException(sprintf(
            'Unsupported geometry type "%s" at %s. Allowed: box, wall_perimeter, gable_roof, cylinder, ngon_prism, cone, wedge.',
            $geomType,
            $path,
        ));
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateArchitecturalSpatial(array $data): void
    {
        $foundation = $data['foundation']['geometry'];
        $walls      = $data['structure']['walls']['geometry'];
        $floor      = $data['structure']['floor']['geometry'];
        $roof       = $data['roof']['geometry'];

        $groundZ = 0.0;
        if (abs((float) $foundation['z'] - $groundZ) > self::EPS) {
            throw new \RuntimeException(
                'Architectural rule: foundation must sit on the ground (foundation.geometry.z ≈ 0).',
            );
        }

        if ('box' !== $foundation['type']) {
            throw new \RuntimeException('Architectural rule: foundation.geometry.type must be "box" (slab).');
        }

        if ('wall_perimeter' !== $walls['type']) {
            throw new \RuntimeException('Architectural rule: structure.walls.geometry.type must be "wall_perimeter".');
        }

        if ('box' !== $floor['type']) {
            throw new \RuntimeException('Architectural rule: structure.floor.geometry.type must be "box".');
        }

        if ('gable_roof' !== $roof['type']) {
            throw new \RuntimeException('Architectural rule: roof.geometry.type must be "gable_roof" for houses/sheds.');
        }

        $slabTop = (float) $foundation['z'] + (float) $foundation['height'];
        $wallBase = (float) $walls['z'];

        if (abs($wallBase - $slabTop) > self::EPS) {
            throw new \RuntimeException(
                'Architectural rule: walls must start on top of the foundation slab (walls.z = foundation.z + foundation.height).',
            );
        }

        $floorBase = (float) $floor['z'];
        if (abs($floorBase - $slabTop) > self::EPS) {
            throw new \RuntimeException(
                'Architectural rule: floor must sit on the slab (floor.z = foundation.z + foundation.height).',
            );
        }

        $wallTop = $wallBase + (float) $walls['height'];
        $roofBase = (float) $roof['z'];

        if (abs($roofBase - $wallTop) > self::EPS) {
            throw new \RuntimeException(
                'Architectural rule: roof must rest on the wall plate (roof.z = walls.z + walls.height).',
            );
        }

        $door = $data['openings']['door'];
        if (null !== $door) {
            $dg = $door['geometry'];
            if ('box' !== $dg['type']) {
                throw new \RuntimeException('Architectural rule: door.geometry.type must be "box".');
            }

            $doorZ = (float) $dg['z'];
            if ($doorZ < $slabTop - self::EPS) {
                throw new \RuntimeException(
                    'Architectural rule: door must be accessible from the ground floor (door.z ≥ slab top).',
                );
            }
        }

        foreach ($data['openings']['windows'] as $i => $win) {
            $wg = $win['geometry'];
            if ('box' !== $wg['type']) {
                throw new \RuntimeException(sprintf('Architectural rule: windows[%d].geometry.type must be "box".', $i));
            }

            $sill = (float) $wg['z'];
            if ($sill < $slabTop - self::EPS) {
                throw new \RuntimeException(
                    sprintf('Architectural rule: window[%d] sill must be at or above floor level (z ≥ slab top).', $i),
                );
            }
        }
    }

    /**
     * @param list<mixed> $parts
     */
    private function validatePartsList(array $parts): void
    {
        foreach ($parts as $i => $part) {
            if (!\is_array($part)) {
                throw new \RuntimeException(sprintf('parts[%d] must be an object.', $i));
            }

            foreach (['id', 'label', 'type', 'x', 'y', 'z'] as $key) {
                if (!\array_key_exists($key, $part)) {
                    throw new \RuntimeException(sprintf('parts[%d] is missing "%s".', $i, $key));
                }
            }

            if (!\is_string($part['type']) || '' === $part['type']) {
                throw new \RuntimeException(sprintf('parts[%d] must have string "type".', $i));
            }

            $this->validatePrimitiveFields(
                sprintf('parts[%d]', $i),
                (string) $part['type'],
                $part,
            );
        }
    }

    /**
     * @return list<string>
     */
    private function architecturalTypesWithQuotes(): array
    {
        return array_map(static fn (string $t): string => sprintf('"%s"', $t), self::ARCHITECTURAL_TYPES);
    }
}
