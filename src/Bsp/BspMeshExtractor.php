<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * Extract render triangles from BSP lumps — ParseFace / ParseTriSurf subset (tr_bsp.c).
 *
 * MST_PATCH tessellation: EXPECTED_NOT_IMPLEMENTED (counted separately).
 * MST_FLARE: skipped (no solid geometry).
 */
final class BspMeshExtractor
{
    /**
     * @return list<BspShader>
     */
    public function loadShaders(BspMap $map): array
    {
        $lump = $map->getLump(BspIdent::LUMP_SHADERS);
        if ($lump->filelen === 0) {
            return [];
        }
        if ($lump->filelen % BspShader::SIZE !== 0) {
            throw new \RuntimeException(sprintf(
                'Shaders lump size %d not multiple of %d',
                $lump->filelen,
                BspShader::SIZE,
            ));
        }

        $bytes = $map->readLumpBytes(BspIdent::LUMP_SHADERS);
        $count = intdiv($lump->filelen, BspShader::SIZE);
        $shaders = [];
        for ($i = 0; $i < $count; $i++) {
            $shaders[] = BspShader::fromBytes($bytes, $i * BspShader::SIZE, $i);
        }

        return $shaders;
    }

    /**
     * @return list<BspDrawVert>
     */
    public function loadDrawVerts(BspMap $map): array
    {
        $lump = $map->getLump(BspIdent::LUMP_DRAWVERTS);
        if ($lump->filelen === 0) {
            return [];
        }
        if ($lump->filelen % BspDrawVert::SIZE !== 0) {
            throw new \RuntimeException(sprintf(
                'Drawverts lump size %d not multiple of %d',
                $lump->filelen,
                BspDrawVert::SIZE,
            ));
        }

        $bytes = $map->readLumpBytes(BspIdent::LUMP_DRAWVERTS);
        $count = intdiv($lump->filelen, BspDrawVert::SIZE);
        $verts = [];
        for ($i = 0; $i < $count; $i++) {
            $verts[] = BspDrawVert::fromBytes($bytes, $i * BspDrawVert::SIZE);
        }

        return $verts;
    }

    /**
     * @return list<int>
     */
    public function loadDrawIndexes(BspMap $map): array
    {
        $lump = $map->getLump(BspIdent::LUMP_DRAWINDEXES);
        if ($lump->filelen === 0) {
            return [];
        }
        if ($lump->filelen % 4 !== 0) {
            throw new \RuntimeException('Drawindexes lump size not multiple of 4');
        }

        $bytes = $map->readLumpBytes(BspIdent::LUMP_DRAWINDEXES);
        return BspBinary::int32Array($bytes, 0, intdiv($lump->filelen, 4));
    }

    /**
     * @return list<BspSurface>
     */
    public function loadSurfaces(BspMap $map): array
    {
        $lump = $map->getLump(BspIdent::LUMP_SURFACES);
        if ($lump->filelen === 0) {
            return [];
        }
        if ($lump->filelen % BspSurface::SIZE !== 0) {
            throw new \RuntimeException(sprintf(
                'Surfaces lump size %d not multiple of %d',
                $lump->filelen,
                BspSurface::SIZE,
            ));
        }

        $bytes = $map->readLumpBytes(BspIdent::LUMP_SURFACES);
        $count = intdiv($lump->filelen, BspSurface::SIZE);
        $surfaces = [];
        for ($i = 0; $i < $count; $i++) {
            $surfaces[] = BspSurface::fromBytes($bytes, $i * BspSurface::SIZE, $i);
        }

        return $surfaces;
    }

    public function extract(BspMap $map): BspWorldMesh
    {
        $drawVerts = $this->loadDrawVerts($map);
        $drawIndexes = $this->loadDrawIndexes($map);
        $surfaces = $this->loadSurfaces($map);

        $vertCount = count($drawVerts);
        $indexCount = count($drawIndexes);

        /** @var list<array{0: float, 1: float, 2: float}> $positions */
        $positions = [];
        /** @var list<int> $outIndices */
        $outIndices = [];
        $typeCounts = [
            'planar' => 0,
            'triangle_soup' => 0,
            'patch' => 0,
            'flare' => 0,
            'bad' => 0,
        ];

        $mins = [999999.0, 999999.0, 999999.0];
        $maxs = [-999999.0, -999999.0, -999999.0];

        foreach ($surfaces as $surf) {
            $typeCounts[$surf->typeName()] = ($typeCounts[$surf->typeName()] ?? 0) + 1;

            if ($surf->surfaceType === BspSurface::MST_PATCH) {
                continue; // EXPECTED_NOT_IMPLEMENTED
            }
            if ($surf->surfaceType === BspSurface::MST_FLARE || $surf->surfaceType === BspSurface::MST_BAD) {
                continue;
            }
            if ($surf->surfaceType !== BspSurface::MST_PLANAR
                && $surf->surfaceType !== BspSurface::MST_TRIANGLE_SOUP) {
                continue;
            }

            if ($surf->numIndexes < 3 || $surf->numIndexes % 3 !== 0) {
                continue;
            }

            $baseVert = $surf->firstVert;
            if ($baseVert < 0 || $surf->numVerts <= 0 || $baseVert + $surf->numVerts > $vertCount) {
                throw new \RuntimeException(sprintf(
                    'Surface %d vert range invalid: first=%d num=%d (drawverts=%d)',
                    $surf->index,
                    $baseVert,
                    $surf->numVerts,
                    $vertCount,
                ));
            }

            $baseIndex = $surf->firstIndex;
            if ($baseIndex < 0 || $baseIndex + $surf->numIndexes > $indexCount) {
                throw new \RuntimeException(sprintf(
                    'Surface %d index range invalid: first=%d num=%d (drawindexes=%d)',
                    $surf->index,
                    $baseIndex,
                    $surf->numIndexes,
                    $indexCount,
                ));
            }

            // Local vertex pool for this surface (RB_SurfaceFace uses local indices into points[]).
            $localBase = count($positions);
            for ($v = 0; $v < $surf->numVerts; $v++) {
                $xyz = $drawVerts[$baseVert + $v]->xyz;
                $positions[] = $xyz;
                for ($a = 0; $a < 3; $a++) {
                    $mins[$a] = min($mins[$a], $xyz[$a]);
                    $maxs[$a] = max($maxs[$a], $xyz[$a]);
                }
            }

            for ($i = 0; $i < $surf->numIndexes; $i++) {
                $localIdx = $drawIndexes[$baseIndex + $i];
                if ($localIdx < 0 || $localIdx >= $surf->numVerts) {
                    throw new \RuntimeException(sprintf(
                        'Surface %d bad local index %d (numVerts=%d)',
                        $surf->index,
                        $localIdx,
                        $surf->numVerts,
                    ));
                }
                $outIndices[] = $localBase + $localIdx;
            }
        }

        if ($positions === []) {
            $mins = [0.0, 0.0, 0.0];
            $maxs = [0.0, 0.0, 0.0];
        }

        return new BspWorldMesh(
            sourceLabel: $map->sourceLabel,
            positions: $positions,
            indices: $outIndices,
            surfaceCount: count($surfaces),
            surfaceTypeCounts: $typeCounts,
            triangleCount: intdiv(count($outIndices), 3),
            mins: $mins,
            maxs: $maxs,
        );
    }
}
