<?php

declare(strict_types=1);

namespace Quake3\Render;

use Quake3\Bsp\BspBinary;
use Quake3\Bsp\BspIdent;
use Quake3\Bsp\BspMap;
use Quake3\Bsp\BspMeshExtractor;
use Quake3\Bsp\BspSurface;
use Quake3\Bsp\LightmapLump;
use Quake3\Collision\CollisionModel;

/**
 * Build static world draw batches (shader + lightmap) including patches.
 *
 * @return array{
 *   batches: list<WorldBatch>,
 *   moverBatches: array<int, list<WorldBatch>>,
 *   lightmaps: LightmapLump,
 *   surfaceTypeCounts: array<string,int>,
 *   triangleCount: int,
 *   mins: list<float>,
 *   maxs: list<float>,
 *   patchCollisionTris: list<array{0:list<float>,1:list<float>,2:list<float>}>,
 *   flares: list<array{origin: list<float>, shader: string, normal: list<float>, color: list<float>}>,
 *   skyShader: ?string,
 *   skipReasons: array<string, int>
 * }
 */
final class WorldBatchBuilder
{
    public function __construct(
        private readonly PatchTessellator $patches = new PatchTessellator(5),
    ) {
    }

    public function build(
        BspMap $map,
        ?ShaderParser $shaderParser = null,
        ?CollisionModel $cm = null,
        ?LightingConfig $lighting = null,
        ?int $debugFace = null,
    ): array {
        $lighting ??= new LightingConfig();
        $ex = new BspMeshExtractor();
        $shaders = $ex->loadShaders($map);
        $drawVerts = $ex->loadDrawVerts($map);
        $drawIndexes = $ex->loadDrawIndexes($map);
        $surfaces = $ex->loadSurfaces($map);
        $lightmaps = LightmapLump::load($map);
        $faceClusters = $this->loadFaceClusters($map);

        // Submodel surfaces (func_door *N) are drawn as SOLID_BMODEL, not world
        /** @var array<int, int> surfaceIndex => modelIndex (>0) */
        $surfaceModel = [];
        $modelBytes = $map->readLumpBytes(BspIdent::LUMP_MODELS);
        $modelCount = intdiv(strlen($modelBytes), 40);
        for ($mi = 1; $mi < $modelCount; $mi++) {
            $o = $mi * 40;
            $firstSurf = BspBinary::int32($modelBytes, $o + 24);
            $numSurf = BspBinary::int32($modelBytes, $o + 28);
            for ($s = 0; $s < $numSurf; $s++) {
                $surfaceModel[$firstSurf + $s] = $mi;
            }
        }

        /** @var array<string, array{shader: string, lm: int, verts: list<array>, indices: list<int>, useLm: bool}> $worldGroups */
        $worldGroups = [];
        /** @var array<int, array<string, array{shader: string, lm: int, verts: list<array>, indices: list<int>, useLm: bool}>> $moverGroups */
        $moverGroups = [];
        $typeCounts = ['planar' => 0, 'triangle_soup' => 0, 'patch' => 0, 'flare' => 0, 'bad' => 0];
        $mins = [999999.0, 999999.0, 999999.0];
        $maxs = [-999999.0, -999999.0, -999999.0];
        $patchCollisionTris = [];
        /** @var list<array{origin: list<float>, shader: string, normal: list<float>, color: list<float>}> $flares */
        $flares = [];
        $skyShaderName = null;
        /** @var list<array{0:list<float>,1:list<float>,2:list<float>,normal:list<float>}> $markTris */
        $markTris = [];
        $skips = [
            'sky' => 0,
            'surf_sky' => 0,
            'surf_nodraw' => 0,
            'flare' => 0,
            'bad' => 0,
            'empty_patch' => 0,
            'bad_indexes' => 0,
            'unknown_type' => 0,
            'debug_face' => 0,
        ];

        foreach ($surfaces as $si => $surf) {
            $modelIdx = $surfaceModel[$si] ?? 0;
            if ($modelIdx > 0) {
                if (!isset($moverGroups[$modelIdx])) {
                    $moverGroups[$modelIdx] = [];
                }
                $groups =& $moverGroups[$modelIdx];
            } else {
                $groups =& $worldGroups;
            }
            $typeCounts[$surf->typeName()] = ($typeCounts[$surf->typeName()] ?? 0) + 1;
            $shaderMeta = $shaders[$surf->shaderNum] ?? null;
            $shaderName = $shaderMeta->shader ?? 'unknown';
            $canMark = $modelIdx === 0 && ($shaderMeta === null
                || (
                    ($shaderMeta->surfaceFlags & (MarkFragments::SURF_NOIMPACT | MarkFragments::SURF_NOMARKS)) === 0
                    && ($shaderMeta->contentFlags & MarkFragments::CONTENTS_FOG) === 0
                ));

            if ($surf->surfaceType === BspSurface::MST_FLARE) {
                // tr_bsp.c ParseFlare: origin = lightmapOrigin (numVerts is 0)
                $origin = $surf->lightmapOrigin;
                // tr_bsp.c: color=lightmapVecs[0], normal=lightmapVecs[2]
                $flares[] = [
                    'origin' => $origin,
                    'shader' => $shaderName,
                    'normal' => $surf->lightmapVecs[2] ?? [0.0, 0.0, 1.0],
                    'color' => $surf->lightmapVecs[0] ?? [1.0, 1.0, 1.0],
                ];
                $skips['flare']++;
                unset($groups);
                continue;
            }
            if ($surf->surfaceType === BspSurface::MST_BAD) {
                $skips['bad']++;
                unset($groups);
                continue;
            }

            $q3 = $shaderParser?->get($shaderName);
            $scriptSky = $q3 !== null && $q3->isSky();
            $flagSky = $shaderMeta !== null && ($shaderMeta->surfaceFlags & MarkFragments::SURF_SKY) !== 0;
            if ($scriptSky || $flagSky) {
                $skyShaderName = $shaderName;
                $skips[$scriptSky ? 'sky' : 'surf_sky']++;
                unset($groups);
                continue;
            }

            if ($debugFace !== null && $si !== $debugFace) {
                $skips['debug_face']++;
                unset($groups);
                continue;
            }

            $lm = $surf->lightmapNum;
            $useLm = $lm >= 0 && $surf->surfaceType !== BspSurface::MST_TRIANGLE_SOUP;
            // ParseTriSurf → ShaderForShaderNum(..., LIGHTMAP_BY_VERTEX)
            $vertexLight = $surf->surfaceType === BspSurface::MST_TRIANGLE_SOUP;
            if ($vertexLight) {
                $lm = -3; // LIGHTMAP_BY_VERTEX sentinel for batch key
                $useLm = false;
            }

            $key = $shaderName . "\0" . $lm;

            if ($surf->surfaceType === BspSurface::MST_PATCH) {
                // tr_bsp.c ParseMesh: SURF_NODRAW still in the leaf list for clip, SF_SKIP for draw.
                if ($shaderMeta !== null
                    && ($shaderMeta->surfaceFlags & MarkFragments::SURF_NODRAW) !== 0) {
                    $skips['surf_nodraw']++;
                    unset($groups);
                    continue;
                }
                $controls = [];
                for ($v = 0; $v < $surf->numVerts; $v++) {
                    $dv = $drawVerts[$surf->firstVert + $v];
                    $controls[] = [
                        'xyz' => $dv->xyz,
                        'st' => $dv->st,
                        'lightmap' => $dv->lightmap,
                        'normal' => $dv->normal,
                        'color' => $dv->color,
                    ];
                }
                $tess = $this->patches->tessellate($controls, $surf->patchWidth, $surf->patchHeight);
                if ($tess['verts'] === []) {
                    $skips['empty_patch']++;
                    unset($groups);
                    continue;
                }
                $this->touchGroup($groups, $key, $shaderName, $lm, $useLm, $vertexLight, null, 0.0);
                $base = count($groups[$key]['verts']);
                foreach ($tess['verts'] as $tv) {
                    $groups[$key]['verts'][] = $tv;
                    if ($modelIdx === 0) {
                        $this->expandBounds($mins, $maxs, $tv['xyz']);
                    }
                }
                foreach ($tess['indices'] as $idx) {
                    $groups[$key]['indices'][] = $base + $idx;
                }
                $this->noteFaceClusters(
                    $groups[$key],
                    $faceClusters,
                    $si,
                    $cm,
                    $modelIdx,
                    $tess['verts'][0]['xyz'] ?? $controls[0]['xyz'],
                );
                $pn = $tess['verts'][0]['normal'] ?? [0.0, 0.0, 1.0];
                $this->pushFace($groups[$key], $surf, $shaderName, $lm, $pn, $base, count($tess['indices']));
                for ($i = 0; $i + 2 < count($tess['indices']); $i += 3) {
                    $ia = $tess['indices'][$i];
                    $ib = $tess['indices'][$i + 1];
                    $ic = $tess['indices'][$i + 2];
                    $a = $tess['verts'][$ia]['xyz'];
                    $b = $tess['verts'][$ib]['xyz'];
                    $c = $tess['verts'][$ic]['xyz'];
                    $e1 = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
                    $e2 = [$c[0] - $a[0], $c[1] - $a[1], $c[2] - $a[2]];
                    $pn = [
                        $e1[1] * $e2[2] - $e1[2] * $e2[1],
                        $e1[2] * $e2[0] - $e1[0] * $e2[2],
                        $e1[0] * $e2[1] - $e1[1] * $e2[0],
                    ];
                    // Draw winding is opposite MakeMeshNormals (Q3). Collision / marks
                    // need the empty-space facing plane, so swap if geom disagrees.
                    $mn = $tess['verts'][$ia]['normal'] ?? [0.0, 0.0, 1.0];
                    if ($pn[0] * $mn[0] + $pn[1] * $mn[1] + $pn[2] * $mn[2] < 0.0) {
                        [$b, $c] = [$c, $b];
                        $pn = [-$pn[0], -$pn[1], -$pn[2]];
                    }
                    if ($modelIdx === 0) {
                        $patchCollisionTris[] = [$a, $b, $c];
                    }
                    if ($canMark) {
                        $plen = sqrt($pn[0] ** 2 + $pn[1] ** 2 + $pn[2] ** 2) ?: 1.0;
                        $markTris[] = [
                            $a,
                            $b,
                            $c,
                            'normal' => [$pn[0] / $plen, $pn[1] / $plen, $pn[2] / $plen],
                        ];
                    }
                }
                unset($groups);
                continue;
            }

            if ($surf->surfaceType !== BspSurface::MST_PLANAR
                && $surf->surfaceType !== BspSurface::MST_TRIANGLE_SOUP) {
                $skips['unknown_type']++;
                unset($groups);
                continue;
            }
            if ($surf->numIndexes < 3 || $surf->numIndexes % 3 !== 0) {
                $skips['bad_indexes']++;
                unset($groups);
                continue;
            }

            // Portal planars / soups keep their own batch (plane for R_MirrorViewBySurface)
            $planeN = null;
            $planeD = 0.0;
            if ($q3 !== null
                && $q3->sort <= Q3Shader::SS_PORTAL + 0.001
                && ($surf->surfaceType === BspSurface::MST_PLANAR
                    || $surf->surfaceType === BspSurface::MST_TRIANGLE_SOUP)
                && $surf->numVerts > 0) {
                if ($surf->surfaceType === BspSurface::MST_PLANAR) {
                    $pn = $surf->lightmapVecs[2] ?? [0.0, 0.0, 1.0];
                    $nlen = sqrt($pn[0] ** 2 + $pn[1] ** 2 + $pn[2] ** 2) ?: 1.0;
                    $planeN = [$pn[0] / $nlen, $pn[1] / $nlen, $pn[2] / $nlen];
                    $v0 = $drawVerts[$surf->firstVert]->xyz;
                    $planeD = $planeN[0] * $v0[0] + $planeN[1] * $v0[1] + $planeN[2] * $v0[2];
                } else {
                    // R_PlaneForSurface SF_TRIANGLES — PlaneFromPoints(first indexed tri)
                    // (avg vertex normals flipped portal_sfx on q3dm0: +Y vs Q3 −Y)
                    $ia = $drawIndexes[$surf->firstIndex];
                    $ib = $drawIndexes[$surf->firstIndex + 1];
                    $ic = $drawIndexes[$surf->firstIndex + 2];
                    $a = $drawVerts[$surf->firstVert + $ia]->xyz;
                    $b = $drawVerts[$surf->firstVert + $ib]->xyz;
                    $c = $drawVerts[$surf->firstVert + $ic]->xyz;
                    $ab = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
                    $ac = [$c[0] - $a[0], $c[1] - $a[1], $c[2] - $a[2]];
                    $nx = $ab[1] * $ac[2] - $ab[2] * $ac[1];
                    $ny = $ab[2] * $ac[0] - $ab[0] * $ac[2];
                    $nz = $ab[0] * $ac[1] - $ab[1] * $ac[0];
                    $nlen = sqrt($nx * $nx + $ny * $ny + $nz * $nz) ?: 1.0;
                    $planeN = [$nx / $nlen, $ny / $nlen, $nz / $nlen];
                    $planeD = $planeN[0] * $a[0] + $planeN[1] * $a[1] + $planeN[2] * $a[2];
                }
                $key .= "\0" . sprintf('%.4f:%.4f:%.4f:%.2f', $planeN[0], $planeN[1], $planeN[2], $planeD);
            }

            $this->touchGroup($groups, $key, $shaderName, $lm, $useLm, $vertexLight, $planeN, $planeD);
            $base = count($groups[$key]['verts']);
            for ($v = 0; $v < $surf->numVerts; $v++) {
                $dv = $drawVerts[$surf->firstVert + $v];
                $groups[$key]['verts'][] = [
                    'xyz' => $dv->xyz,
                    'st' => $dv->st,
                    'lightmap' => $useLm ? $dv->lightmap : [0.0, 0.0],
                    'normal' => $dv->normal,
                    'color' => $dv->color,
                ];
                if ($modelIdx === 0) {
                    $this->expandBounds($mins, $maxs, $dv->xyz);
                }
            }
            for ($i = 0; $i < $surf->numIndexes; $i++) {
                $groups[$key]['indices'][] = $base + $drawIndexes[$surf->firstIndex + $i];
            }
            $this->noteFaceClusters(
                $groups[$key],
                $faceClusters,
                $si,
                $cm,
                $modelIdx,
                $drawVerts[$surf->firstVert]->xyz,
            );
            $faceN = $surf->surfaceType === BspSurface::MST_PLANAR
                ? ($surf->lightmapVecs[2] ?? [0.0, 0.0, 1.0])
                : [0.0, 0.0, 1.0];
            $this->pushFace($groups[$key], $surf, $shaderName, $lm, $faceN, $base, $surf->numIndexes);
            if ($canMark && $surf->surfaceType === BspSurface::MST_PLANAR) {
                $planeN = $surf->lightmapVecs[2] ?? [0.0, 0.0, 1.0];
                $nlen = sqrt($planeN[0] ** 2 + $planeN[1] ** 2 + $planeN[2] ** 2) ?: 1.0;
                $planeN = [$planeN[0] / $nlen, $planeN[1] / $nlen, $planeN[2] / $nlen];
                for ($i = 0; $i + 2 < $surf->numIndexes; $i += 3) {
                    $ia = $drawIndexes[$surf->firstIndex + $i];
                    $ib = $drawIndexes[$surf->firstIndex + $i + 1];
                    $ic = $drawIndexes[$surf->firstIndex + $i + 2];
                    $markTris[] = [
                        $drawVerts[$surf->firstVert + $ia]->xyz,
                        $drawVerts[$surf->firstVert + $ib]->xyz,
                        $drawVerts[$surf->firstVert + $ic]->xyz,
                        'normal' => $planeN,
                    ];
                }
            }
            unset($groups);
        }

        [$batches, $triTotal] = $this->groupsToBatches($worldGroups, $shaderParser, $cm, $lighting, false);
        /** @var array<int, list<WorldBatch>> $moverBatches */
        $moverBatches = [];
        foreach ($moverGroups as $mi => $mg) {
            [$mb] = $this->groupsToBatches($mg, $shaderParser, $cm, $lighting, true);
            if ($mb !== []) {
                $moverBatches[$mi] = $mb;
            }
        }

        if ($mins[0] > $maxs[0]) {
            $mins = [0.0, 0.0, 0.0];
            $maxs = [0.0, 0.0, 0.0];
        }

        return [
            'batches' => $batches,
            'moverBatches' => $moverBatches,
            'lightmaps' => $lightmaps,
            'surfaceTypeCounts' => $typeCounts,
            'triangleCount' => $triTotal,
            'mins' => $mins,
            'maxs' => $maxs,
            'patchCollisionTris' => $patchCollisionTris,
            'flares' => $flares,
            'skyShader' => $skyShaderName,
            'markTris' => $markTris,
            'skipReasons' => $skips,
        ];
    }

    /**
     * @param array<string, array{shader: string, lm: int, verts: list<array>, indices: list<int>, useLm: bool, vertexLight?: bool, planeNormal?: ?list<float>, planeDist?: float}> $groups
     * @return array{0: list<WorldBatch>, 1: int}
     */
    private function groupsToBatches(
        array $groups,
        ?ShaderParser $shaderParser,
        ?CollisionModel $cm,
        LightingConfig $lighting,
        bool $alwaysVisible,
    ): array {
        $batches = [];
        $triTotal = 0;
        foreach ($groups as $g) {
            if ($g['indices'] === []) {
                continue;
            }
            $vbin = '';
            $cbin = '';
            $bmins = [999999.0, 999999.0, 999999.0];
            $bmaxs = [-999999.0, -999999.0, -999999.0];
            foreach ($g['verts'] as $v) {
                $vbin .= pack(
                    'g*',
                    $v['xyz'][0], $v['xyz'][1], $v['xyz'][2],
                    $v['st'][0], $v['st'][1],
                    $v['lightmap'][0], $v['lightmap'][1],
                    $v['normal'][0] ?? 0.0,
                    $v['normal'][1] ?? 0.0,
                    $v['normal'][2] ?? 1.0,
                );
                $col = $v['color'] ?? [255, 255, 255, 255];
                // tr_bsp.c ParseFace/ParseTriSurf: R_ColorShiftLightingBytes (webgl ×4)
                [$cr, $cg, $cb, $ca] = $lighting->colorShiftBytes(
                    (int) $col[0],
                    (int) $col[1],
                    (int) $col[2],
                    (int) ($col[3] ?? 255),
                );
                $cbin .= chr($cr) . chr($cg) . chr($cb) . chr($ca);
                $this->expandBounds($bmins, $bmaxs, $v['xyz']);
            }
            $ibin = '';
            foreach ($g['indices'] as $idx) {
                $ibin .= pack('V', $idx);
            }
            $tri = intdiv(count($g['indices']), 3);
            $triTotal += $tri;
            $q3 = $shaderParser?->get($g['shader']);
            $blend = $q3 !== null && $q3->needsBlendPass();
            $autosprites = [];
            $autosprite2s = [];
            if ($q3 !== null && $q3->hasAutosprite()) {
                $autosprites = $this->extractAutosprites($g['verts'], $g['indices']);
            }
            if ($q3 !== null && $q3->hasAutosprite2()) {
                $autosprite2s = $this->extractAutosprite2s($g['verts'], $g['indices']);
            }
            $cluster = -1;
            /** @var list<int> $clusterIds */
            $clusterIds = [];
            if (!$alwaysVisible) {
                $clusterIds = array_map('intval', array_keys($g['clusters'] ?? []));
                sort($clusterIds);
                $cluster = $clusterIds[0] ?? -1;
            }
            $batches[] = new WorldBatch(
                shaderName: $g['shader'],
                lightmapNum: $g['lm'],
                vertexBinary: $vbin,
                indexBinary: $ibin,
                indexCount: count($g['indices']),
                triangleCount: $tri,
                useLightmap: $g['useLm'],
                blend: $blend,
                sky: false,
                cluster: $cluster,
                mins: $bmins,
                maxs: $bmaxs,
                autosprites: $autosprites,
                sort: $q3?->sort ?? Q3Shader::SS_OPAQUE,
                polygonOffset: $q3?->polygonOffset ?? false,
                colorBinary: $cbin,
                autosprite2s: $autosprite2s,
                vertexLight: !empty($g['vertexLight']),
                planeNormal: $g['planeNormal'] ?? null,
                planeDist: (float) ($g['planeDist'] ?? 0.0),
                clusters: $clusterIds,
                faces: $g['faces'] ?? [],
            );
        }

        usort($batches, static function (WorldBatch $a, WorldBatch $b): int {
            if ($a->sort !== $b->sort) {
                return $a->sort <=> $b->sort;
            }
            if ($a->blend !== $b->blend) {
                return $a->blend ? 1 : -1;
            }
            return strcmp($a->shaderName, $b->shaderName);
        });

        return [$batches, $triTotal];
    }

    /** @param list<float> $mins @param list<float> $maxs @param list<float> $xyz */
    private function expandBounds(array &$mins, array &$maxs, array $xyz): void
    {
        for ($a = 0; $a < 3; $a++) {
            $mins[$a] = min($mins[$a], $xyz[$a]);
            $maxs[$a] = max($maxs[$a], $xyz[$a]);
        }
    }

    /**
     * @param array<string, array<string, mixed>> $groups
     */
    private function touchGroup(
        array &$groups,
        string $key,
        string $shaderName,
        int $lm,
        bool $useLm,
        bool $vertexLight,
        ?array $planeN,
        float $planeD,
    ): void {
        if (isset($groups[$key])) {
            return;
        }
        $groups[$key] = [
            'shader' => $shaderName,
            'lm' => $lm,
            'verts' => [],
            'indices' => [],
            'useLm' => $useLm,
            'vertexLight' => $vertexLight,
            'planeNormal' => $planeN,
            'planeDist' => $planeD,
            'clusters' => [],
            'faces' => [],
        ];
    }

    /**
     * R_AddWorldSurface: clusters come from dleafs that list this face (LUMP_LEAFSURFACES),
     * not pointLeafnum of a vertex (verts often sit in solid, cluster=-1).
     *
     * @return array<int, array<int, true>> faceIndex => cluster set
     */
    private function loadFaceClusters(BspMap $map): array
    {
        $leafBytes = $map->readLumpBytes(BspIdent::LUMP_LEAFS);
        $lsBytes = $map->readLumpBytes(BspIdent::LUMP_LEAFSURFACES);
        $leafCount = intdiv(strlen($leafBytes), 48);
        $lsCount = intdiv(strlen($lsBytes), 4);
        /** @var array<int, array<int, true>> $out */
        $out = [];
        for ($i = 0; $i < $leafCount; $i++) {
            $o = $i * 48;
            $cluster = BspBinary::int32($leafBytes, $o);
            if ($cluster < 0) {
                continue;
            }
            $first = BspBinary::int32($leafBytes, $o + 32);
            $num = BspBinary::int32($leafBytes, $o + 36);
            for ($j = 0; $j < $num; $j++) {
                $idx = $first + $j;
                if ($idx < 0 || $idx >= $lsCount) {
                    continue;
                }
                $face = BspBinary::int32($lsBytes, $idx * 4);
                if ($face < 0) {
                    continue;
                }
                $out[$face][$cluster] = true;
            }
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $group
     * @param array<int, array<int, true>> $faceClusters
     * @param list<float> $xyz
     */
    private function noteFaceClusters(
        array &$group,
        array $faceClusters,
        int $si,
        ?CollisionModel $cm,
        int $modelIdx,
        array $xyz,
    ): void {
        if ($modelIdx !== 0) {
            return;
        }
        if (isset($faceClusters[$si])) {
            foreach ($faceClusters[$si] as $c => $_) {
                $group['clusters'][(int) $c] = true;
            }
            return;
        }
        $this->noteFaceCluster($group, $cm, $modelIdx, $xyz);
    }

    /**
     * @param array<string, mixed> $group
     * @param list<float> $xyz
     */
    private function noteFaceCluster(array &$group, ?CollisionModel $cm, int $modelIdx, array $xyz): void
    {
        if ($cm === null || $modelIdx !== 0 || $xyz === []) {
            return;
        }
        $leaf = $cm->pointLeafnum($xyz);
        $c = $cm->leafCluster($leaf);
        if ($c >= 0) {
            $group['clusters'][$c] = true;
        }
    }

    /**
     * @param array<string, mixed> $group
     * @param list<float> $normal
     */
    private function pushFace(
        array &$group,
        BspSurface $surf,
        string $shaderName,
        int $lm,
        array $normal,
        int $localVertBase,
        int $indexCount,
    ): void {
        $nlen = sqrt(($normal[0] ?? 0.0) ** 2 + ($normal[1] ?? 0.0) ** 2 + ($normal[2] ?? 0.0) ** 2) ?: 1.0;
        $localIndexStart = count($group['indices']) - $indexCount;
        $group['faces'][] = [
            'index' => $surf->index,
            'type' => $surf->surfaceType,
            'typeName' => $surf->typeName(),
            'shaderNum' => $surf->shaderNum,
            'shader' => $shaderName,
            'fog' => $surf->fogNum,
            'lm' => $lm,
            'firstVert' => $surf->firstVert,
            'numVerts' => $surf->numVerts,
            'firstIndex' => $surf->firstIndex,
            'numIndexes' => $surf->numIndexes,
            'normal' => [
                ($normal[0] ?? 0.0) / $nlen,
                ($normal[1] ?? 0.0) / $nlen,
                ($normal[2] ?? 0.0) / $nlen,
            ],
            'localVertBase' => $localVertBase,
            'localIndexStart' => max(0, $localIndexStart),
            'localIndexCount' => $indexCount,
            'triCount' => intdiv($indexCount, 3),
        ];
    }

    /**
     * AutospriteDeform prep: each index sextet → quad mid + radius (tr_shade_calc.c).
     *
     * @param list<array{xyz: list<float>}> $verts
     * @param list<int> $indices
     * @return list<array{mid: list<float>, radius: float}>
     */
    private function extractAutosprites(array $verts, array $indices): array
    {
        $sprites = [];
        $n = count($indices);
        for ($i = 0; $i + 5 < $n; $i += 6) {
            $ids = [
                $indices[$i],
                $indices[$i + 1],
                $indices[$i + 2],
                $indices[$i + 3],
                $indices[$i + 4],
                $indices[$i + 5],
            ];
            $uniq = [];
            foreach ($ids as $id) {
                $uniq[$id] = true;
            }
            $keys = array_keys($uniq);
            if (count($keys) !== 4) {
                continue;
            }
            $mid = [0.0, 0.0, 0.0];
            foreach ($keys as $id) {
                $p = $verts[$id]['xyz'];
                $mid[0] += $p[0];
                $mid[1] += $p[1];
                $mid[2] += $p[2];
            }
            $mid = [$mid[0] * 0.25, $mid[1] * 0.25, $mid[2] * 0.25];
            $p0 = $verts[$keys[0]]['xyz'];
            $dx = $p0[0] - $mid[0];
            $dy = $p0[1] - $mid[1];
            $dz = $p0[2] - $mid[2];
            $radius = sqrt($dx * $dx + $dy * $dy + $dz * $dz) * 0.70710678;
            if ($radius < 0.01) {
                continue;
            }
            $sprites[] = ['mid' => $mid, 'radius' => $radius];
        }
        return $sprites;
    }

    /**
     * Autosprite2Deform prep: quads + two shortest edges (tr_shade_calc.c).
     *
     * @param list<array{xyz: list<float>, st?: list<float>}> $verts
     * @param list<int> $indices
     * @return list<array{verts: list<list<float>>, sts: list<list<float>>, shortEdges: list<array{a:int,b:int,len2:float,flip:bool}>}>
     */
    private function extractAutosprite2s(array $verts, array $indices): array
    {
        $edgeVerts = [[0, 1], [0, 2], [0, 3], [1, 2], [1, 3], [2, 3]];
        $out = [];
        $n = count($indices);
        for ($i = 0; $i + 5 < $n; $i += 6) {
            $a = $indices[$i];
            $b = $indices[$i + 1];
            $c = $indices[$i + 2];
            $d = null;
            foreach ([$indices[$i + 3], $indices[$i + 4], $indices[$i + 5]] as $x) {
                if ($x !== $a && $x !== $b && $x !== $c) {
                    $d = $x;
                    break;
                }
            }
            if ($d === null) {
                continue;
            }
            $ids = [$a, $b, $c, $d];
            $xyz = [];
            $sts = [];
            foreach ($ids as $id) {
                $xyz[] = $verts[$id]['xyz'];
                $sts[] = $verts[$id]['st'] ?? [0.0, 0.0];
            }
            $localIdx = [];
            foreach (array_slice($indices, $i, 6) as $gi) {
                $li = array_search($gi, $ids, true);
                if ($li === false) {
                    continue 2;
                }
                $localIdx[] = $li;
            }

            $nums = [0, 0];
            $lengths = [999999.0, 999999.0];
            for ($j = 0; $j < 6; $j++) {
                $v1 = $xyz[$edgeVerts[$j][0]];
                $v2 = $xyz[$edgeVerts[$j][1]];
                $dx = $v1[0] - $v2[0];
                $dy = $v1[1] - $v2[1];
                $dz = $v1[2] - $v2[2];
                $l = $dx * $dx + $dy * $dy + $dz * $dz;
                if ($l < $lengths[0]) {
                    $nums[1] = $nums[0];
                    $lengths[1] = $lengths[0];
                    $nums[0] = $j;
                    $lengths[0] = $l;
                } elseif ($l < $lengths[1]) {
                    $nums[1] = $j;
                    $lengths[1] = $l;
                }
            }

            $shortEdges = [];
            for ($j = 0; $j < 2; $j++) {
                $e0 = $edgeVerts[$nums[$j]][0];
                $e1 = $edgeVerts[$nums[$j]][1];
                // Q3: if edge appears in index winding as (e0,e1), use -minor on v1; else +minor
                $flip = true;
                for ($k = 0; $k < 5; $k++) {
                    if ($localIdx[$k] === $e0 && $localIdx[$k + 1] === $e1) {
                        $flip = false;
                        break;
                    }
                }
                $shortEdges[] = ['a' => $e0, 'b' => $e1, 'len2' => $lengths[$j], 'flip' => $flip];
            }

            $out[] = [
                'verts' => $xyz,
                'sts' => $sts,
                'shortEdges' => $shortEdges,
            ];
        }
        return $out;
    }
}
