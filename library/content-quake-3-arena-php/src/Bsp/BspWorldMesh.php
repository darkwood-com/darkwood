<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * CPU-side world mesh extracted from BSP (planar + triangle_soup only).
 */
final class BspWorldMesh
{
    /**
     * @param list<array{0: float, 1: float, 2: float}> $positions indexed vertex positions
     * @param list<int> $indices triangle list (groups of 3)
     * @param array<string, int> $surfaceTypeCounts
     * @param array{0: float, 1: float, 2: float} $mins
     * @param array{0: float, 1: float, 2: float} $maxs
     */
    public function __construct(
        public readonly string $sourceLabel,
        public readonly array $positions,
        public readonly array $indices,
        public readonly int $surfaceCount,
        public readonly array $surfaceTypeCounts,
        public readonly int $triangleCount,
        public readonly array $mins,
        public readonly array $maxs,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toJsonArray(bool $includeIndices = true): array
    {
        $flat = [];
        foreach ($this->positions as $p) {
            $flat[] = $p[0];
            $flat[] = $p[1];
            $flat[] = $p[2];
        }

        $data = [
            'source' => $this->sourceLabel,
            'vertexCount' => count($this->positions),
            'triangleCount' => $this->triangleCount,
            'surfaceCount' => $this->surfaceCount,
            'surfaceTypeCounts' => $this->surfaceTypeCounts,
            'bounds' => ['mins' => $this->mins, 'maxs' => $this->maxs],
            'positions' => $flat,
        ];

        if ($includeIndices) {
            $data['indices'] = $this->indices;
        }

        return $data;
    }

    /** Packed float32 XYZ for OpenGL VBO upload. */
    public function positionsBinary(): string
    {
        $bin = '';
        foreach ($this->positions as $p) {
            $bin .= pack('g*', $p[0], $p[1], $p[2]);
        }

        return $bin;
    }

    /** Packed uint32 indices for OpenGL IBO upload. */
    public function indicesBinary(): string
    {
        $bin = '';
        foreach ($this->indices as $i) {
            $bin .= pack('V', $i);
        }

        return $bin;
    }
}
