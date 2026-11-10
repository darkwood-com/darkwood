<?php

declare(strict_types=1);

namespace Quake3\Collision;

use Quake3\Bsp\BspBinary;
use Quake3\Bsp\BspIdent;
use Quake3\Bsp\BspMap;

/**
 * World clip map + CM_BoxTrace (brushes only; patches EXPECTED_NOT_IMPLEMENTED).
 * Port of CMod_Load* / CM_BoxTrace from cm_load.c / cm_trace.c.
 */
final class CollisionModel
{
    public const CONTENTS_SOLID = 1;
    public const CONTENTS_PLAYERCLIP = 0x10000;
    public const CONTENTS_BODY = 0x2000000;
    public const CONTENTS_CORPSE = 0x4000000;
    public const MASK_PLAYERSOLID = self::CONTENTS_SOLID | self::CONTENTS_PLAYERCLIP | self::CONTENTS_BODY;
    /** Bullet_Fire / fire_plasma — ignores PLAYERCLIP */
    public const MASK_SHOT = self::CONTENTS_SOLID | self::CONTENTS_BODY | self::CONTENTS_CORPSE;

    private const SURFACE_CLIP_EPSILON = 0.125;
    private const MAX_POSITION_LEAFS = 1024;

    private const SHADER_SIZE = 72; // MAX_QPATH 64 + 2 ints
    private const PLANE_SIZE = 16;
    private const NODE_SIZE = 36;
    private const LEAF_SIZE = 48;
    private const BRUSH_SIZE = 12;
    private const BRUSHSIDE_SIZE = 8;

    /** @var list<array{normal: list<float>, dist: float, type: int, signbits: int}> */
    private array $planes = [];
    /** @var list<array{plane: int, children: array{int, int}}> */
    private array $nodes = [];
    /** @var list<array{cluster: int, area: int, firstLeafBrush: int, numLeafBrushes: int}> */
    private array $leafs = [];
    /** @var list<int> */
    private array $leafbrushes = [];
    /** @var list<array{firstSide: int, numsides: int, contents: int, bounds: array{list<float>, list<float>}, checkcount: int}> */
    private array $brushes = [];
    /** @var list<array{plane: int, surfaceFlags: int}> */
    private array $brushsides = [];
    /** @var list<array{contentFlags: int, surfaceFlags: int}> */
    private array $shaders = [];

    private int $checkcount = 0;
    private int $numNodes = 0;

    /**
     * Temporary entity clip boxes (func_door AABBs, etc.).
     * @var list<array{mins: list<float>, maxs: list<float>}>
     */
    private array $entityBoxes = [];

    /** @param list<array{mins: list<float>, maxs: list<float>}> $boxes */
    public function setEntityBoxes(array $boxes): void
    {
        $this->entityBoxes = $boxes;
    }

    /**
     * Tessellated patch triangles for collision (facet approximation of CM_GeneratePatchCollide).
     * @var list<array{a: list<float>, b: list<float>, c: list<float>, n: list<float>, d: float, mins: list<float>, maxs: list<float>}>
     */
    private array $patchTris = [];

    public static function load(BspMap $map): self
    {
        $cm = new self();
        $cm->loadShaders($map->readLumpBytes(BspIdent::LUMP_SHADERS));
        $cm->loadPlanes($map->readLumpBytes(BspIdent::LUMP_PLANES));
        $cm->loadBrushSides($map->readLumpBytes(BspIdent::LUMP_BRUSHSIDES));
        $cm->loadBrushes($map->readLumpBytes(BspIdent::LUMP_BRUSHES));
        $cm->loadLeafs($map->readLumpBytes(BspIdent::LUMP_LEAFS));
        $cm->loadLeafBrushes($map->readLumpBytes(BspIdent::LUMP_LEAFBRUSHES));
        $cm->loadNodes($map->readLumpBytes(BspIdent::LUMP_NODES));
        return $cm;
    }

    /** CM_PointLeafnum — leaf index containing point. */
    public function pointLeafnum(array $p): int
    {
        $num = 0;
        while ($num >= 0) {
            $node = $this->nodes[$num];
            $plane = $this->planes[$node['plane']];
            $d = $plane['normal'][0] * $p[0] + $plane['normal'][1] * $p[1] + $plane['normal'][2] * $p[2] - $plane['dist'];
            $num = $d >= 0 ? $node['children'][0] : $node['children'][1];
        }
        return -1 - $num;
    }

    public function leafCluster(int $leafnum): int
    {
        return $this->leafs[$leafnum]['cluster'] ?? -1;
    }

    /**
     * @param list<array{0: list<float>, 1: list<float>, 2: list<float>}> $tris
     */
    public function setPatchCollisionTris(array $tris): void
    {
        $this->patchTris = [];
        foreach ($tris as $t) {
            $a = $t[0];
            $b = $t[1];
            $c = $t[2];
            $ab = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
            $ac = [$c[0] - $a[0], $c[1] - $a[1], $c[2] - $a[2]];
            $n = [
                $ab[1] * $ac[2] - $ab[2] * $ac[1],
                $ab[2] * $ac[0] - $ab[0] * $ac[2],
                $ab[0] * $ac[1] - $ab[1] * $ac[0],
            ];
            $len = sqrt($n[0] * $n[0] + $n[1] * $n[1] + $n[2] * $n[2]);
            if ($len < 1e-6) {
                continue;
            }
            $n = [$n[0] / $len, $n[1] / $len, $n[2] / $len];
            $d = $n[0] * $a[0] + $n[1] * $a[1] + $n[2] * $a[2];
            $mins = [
                min($a[0], $b[0], $c[0]),
                min($a[1], $b[1], $c[1]),
                min($a[2], $b[2], $c[2]),
            ];
            $maxs = [
                max($a[0], $b[0], $c[0]),
                max($a[1], $b[1], $c[1]),
                max($a[2], $b[2], $c[2]),
            ];
            $this->patchTris[] = [
                'a' => $a, 'b' => $b, 'c' => $c,
                'n' => $n, 'd' => $d,
                'mins' => $mins, 'maxs' => $maxs,
            ];
        }
    }

    /**
     * CM_BoxTrace world model 0.
     *
     * @param list<float> $start
     * @param list<float> $end
     * @param list<float> $mins
     * @param list<float> $maxs
     */
    public function trace(array $start, array $end, array $mins, array $maxs, int $contentMask = self::MASK_PLAYERSOLID): TraceResult
    {
        $this->checkcount++;

        $tw = [
            'fraction' => 1.0,
            'startSolid' => false,
            'allSolid' => false,
            'contents' => 0,
            'surfaceFlags' => 0,
            'planeNormal' => null,
            'planeDist' => 0.0,
            'brushmask' => $contentMask,
            'entityNum' => 0,
            'start' => [0.0, 0.0, 0.0],
            'end' => [0.0, 0.0, 0.0],
            'size0' => [0.0, 0.0, 0.0],
            'size1' => [0.0, 0.0, 0.0],
            'offsets' => [],
            'extents' => [0.0, 0.0, 0.0],
            'bounds0' => [0.0, 0.0, 0.0],
            'bounds1' => [0.0, 0.0, 0.0],
            'isPoint' => false,
        ];

        if ($this->numNodes === 0) {
            return new TraceResult(endPos: $end);
        }

        $offset = [0.0, 0.0, 0.0];
        for ($i = 0; $i < 3; $i++) {
            $offset[$i] = ($mins[$i] + $maxs[$i]) * 0.5;
            $tw['size0'][$i] = $mins[$i] - $offset[$i];
            $tw['size1'][$i] = $maxs[$i] - $offset[$i];
            $tw['start'][$i] = $start[$i] + $offset[$i];
            $tw['end'][$i] = $end[$i] + $offset[$i];
        }

        // offsets[signbits] — cm_trace.c:1208-1239
        $s0 = $tw['size0'];
        $s1 = $tw['size1'];
        $tw['offsets'] = [
            [$s0[0], $s0[1], $s0[2]],
            [$s1[0], $s0[1], $s0[2]],
            [$s0[0], $s1[1], $s0[2]],
            [$s1[0], $s1[1], $s0[2]],
            [$s0[0], $s0[1], $s1[2]],
            [$s1[0], $s0[1], $s1[2]],
            [$s0[0], $s1[1], $s1[2]],
            [$s1[0], $s1[1], $s1[2]],
        ];

        for ($i = 0; $i < 3; $i++) {
            if ($tw['start'][$i] < $tw['end'][$i]) {
                $tw['bounds0'][$i] = $tw['start'][$i] + $tw['size0'][$i];
                $tw['bounds1'][$i] = $tw['end'][$i] + $tw['size1'][$i];
            } else {
                $tw['bounds0'][$i] = $tw['end'][$i] + $tw['size0'][$i];
                $tw['bounds1'][$i] = $tw['start'][$i] + $tw['size1'][$i];
            }
        }

        if ($start[0] === $end[0] && $start[1] === $end[1] && $start[2] === $end[2]) {
            $this->positionTest($tw);
        } else {
            if ($tw['size0'][0] == 0.0 && $tw['size0'][1] == 0.0 && $tw['size0'][2] == 0.0) {
                $tw['isPoint'] = true;
                $tw['extents'] = [0.0, 0.0, 0.0];
            } else {
                $tw['isPoint'] = false;
                $tw['extents'] = [$tw['size1'][0], $tw['size1'][1], $tw['size1'][2]];
            }
            $this->traceThroughTree($tw, 0, 0.0, 1.0, $tw['start'], $tw['end']);
        }

        if (($contentMask & self::CONTENTS_SOLID) !== 0 && $this->patchTris !== []) {
            $this->tracePatchTris($tw);
        }
        if (($contentMask & self::CONTENTS_SOLID) !== 0 && $this->entityBoxes !== []) {
            $this->traceEntityBoxes($tw, $start, $end, $mins, $maxs);
        }

        $fraction = $tw['fraction'];
        if ($fraction === 1.0) {
            $endPos = $end;
        } else {
            $endPos = [
                $start[0] + $fraction * ($end[0] - $start[0]),
                $start[1] + $fraction * ($end[1] - $start[1]),
                $start[2] + $fraction * ($end[2] - $start[2]),
            ];
        }

        return new TraceResult(
            fraction: $fraction,
            endPos: $endPos,
            planeNormal: $tw['planeNormal'],
            planeDist: $tw['planeDist'],
            startSolid: $tw['startSolid'],
            allSolid: $tw['allSolid'],
            contents: $tw['contents'],
            surfaceFlags: $tw['surfaceFlags'],
            entityNum: (int) ($tw['entityNum'] ?? 0),
        );
    }

    /**
     * Swept AABB vs temporary entity boxes (doors / movers).
     * @param array<string, mixed> $tw
     * @param list<float> $start @param list<float> $end
     * @param list<float> $mins @param list<float> $maxs
     */
    private function traceEntityBoxes(array &$tw, array $start, array $end, array $mins, array $maxs): void
    {
        $dir = [
            $end[0] - $start[0],
            $end[1] - $start[1],
            $end[2] - $start[2],
        ];
        $isPoint = ($dir[0] === 0.0 && $dir[1] === 0.0 && $dir[2] === 0.0);

        foreach ($this->entityBoxes as $ei => $box) {
            // Minkowski: expand entity by player box so we can treat player as a point
            $emins = [
                $box['mins'][0] - $maxs[0],
                $box['mins'][1] - $maxs[1],
                $box['mins'][2] - $maxs[2],
            ];
            $emaxs = [
                $box['maxs'][0] - $mins[0],
                $box['maxs'][1] - $mins[1],
                $box['maxs'][2] - $mins[2],
            ];

            if ($isPoint) {
                if ($start[0] >= $emins[0] && $start[0] <= $emaxs[0]
                    && $start[1] >= $emins[1] && $start[1] <= $emaxs[1]
                    && $start[2] >= $emins[2] && $start[2] <= $emaxs[2]
                ) {
                    $tw['startSolid'] = true;
                    $tw['allSolid'] = true;
                    $tw['fraction'] = 0.0;
                    $tw['contents'] = self::CONTENTS_SOLID;
                    $tw['entityNum'] = $ei + 1;
                    $tw['planeNormal'] = [0.0, 0.0, 1.0];
                    $tw['planeDist'] = $emaxs[2];
                }
                continue;
            }

            $enter = 0.0;
            $exit = 1.0;
            $hitAxis = -1;
            $hitSign = 0.0;
            $miss = false;
            for ($i = 0; $i < 3; $i++) {
                if (abs($dir[$i]) < 1e-8) {
                    if ($start[$i] < $emins[$i] || $start[$i] > $emaxs[$i]) {
                        $miss = true;
                        break;
                    }
                    continue;
                }
                $t1 = ($emins[$i] - $start[$i]) / $dir[$i];
                $t2 = ($emaxs[$i] - $start[$i]) / $dir[$i];
                if ($t1 > $t2) {
                    $tmp = $t1;
                    $t1 = $t2;
                    $t2 = $tmp;
                }
                // dir>0 enters emins (normal −axis); dir<0 enters emaxs (normal +axis)
                $faceSign = $dir[$i] > 0.0 ? -1.0 : 1.0;
                if ($t1 > $enter) {
                    $enter = $t1;
                    $hitAxis = $i;
                    $hitSign = $faceSign;
                }
                if ($t2 < $exit) {
                    $exit = $t2;
                }
                if ($enter > $exit) {
                    $miss = true;
                    break;
                }
            }
            if ($miss || $enter >= $tw['fraction'] || $exit < 0.0) {
                continue;
            }
            if ($enter < 0.0) {
                // started inside
                $tw['startSolid'] = true;
                if ($exit < $tw['fraction']) {
                    // still overlapping at end of move
                    if ($exit <= 0.0) {
                        $tw['allSolid'] = true;
                        $tw['fraction'] = 0.0;
                    }
                }
                $tw['contents'] = self::CONTENTS_SOLID;
                $tw['entityNum'] = $ei + 1;
                continue;
            }

            $tw['fraction'] = $enter;
            $tw['contents'] = self::CONTENTS_SOLID;
            $tw['entityNum'] = $ei + 1;
            $n = [0.0, 0.0, 0.0];
            if ($hitAxis >= 0) {
                $n[$hitAxis] = $hitSign;
            } else {
                $n[2] = 1.0;
            }
            $tw['planeNormal'] = $n;
            $hitPos = [
                $start[0] + $dir[0] * $enter,
                $start[1] + $dir[1] * $enter,
                $start[2] + $dir[2] * $enter,
            ];
            $tw['planeDist'] = $n[0] * $hitPos[0] + $n[1] * $hitPos[1] + $n[2] * $hitPos[2];
        }
    }

    /**
     * Box vs patch triangles — expanded plane clip (CM_TraceThroughPatch facet style subset).
     * @param array<string, mixed> $tw
     */
    private function tracePatchTris(array &$tw): void
    {
        $ext = $tw['extents'];
        $pad = [$ext[0] + 1, $ext[1] + 1, $ext[2] + 1];
        foreach ($this->patchTris as $tri) {
            // sweep AABB vs tri AABB
            if ($tw['bounds1'][0] < $tri['mins'][0] - $pad[0]
                || $tw['bounds0'][0] > $tri['maxs'][0] + $pad[0]
                || $tw['bounds1'][1] < $tri['mins'][1] - $pad[1]
                || $tw['bounds0'][1] > $tri['maxs'][1] + $pad[1]
                || $tw['bounds1'][2] < $tri['mins'][2] - $pad[2]
                || $tw['bounds0'][2] > $tri['maxs'][2] + $pad[2]
            ) {
                continue;
            }

            $n = $tri['n'];
            $signbits = 0;
            if ($n[0] < 0) {
                $signbits |= 1;
            }
            if ($n[1] < 0) {
                $signbits |= 2;
            }
            if ($n[2] < 0) {
                $signbits |= 4;
            }
            $off = $tw['offsets'][$signbits];
            $dist = $tri['d'] - ($off[0] * $n[0] + $off[1] * $n[1] + $off[2] * $n[2]);
            $d1 = $tw['start'][0] * $n[0] + $tw['start'][1] * $n[1] + $tw['start'][2] * $n[2] - $dist;
            $d2 = $tw['end'][0] * $n[0] + $tw['end'][1] * $n[1] + $tw['end'][2] * $n[2] - $dist;

            if ($d1 > 0 && ($d2 >= self::SURFACE_CLIP_EPSILON || $d2 >= $d1)) {
                continue;
            }
            if ($d1 <= 0 && $d2 <= 0) {
                continue;
            }
            if ($d1 <= $d2) {
                continue; // leaving
            }
            $f = ($d1 - self::SURFACE_CLIP_EPSILON) / ($d1 - $d2);
            if ($f < 0) {
                $f = 0;
            }
            if ($f >= $tw['fraction']) {
                continue;
            }
            // hit point in expanded space — barycentric on triangle (approximate with plane hit)
            $hit = [
                $tw['start'][0] + $f * ($tw['end'][0] - $tw['start'][0]),
                $tw['start'][1] + $f * ($tw['end'][1] - $tw['start'][1]),
                $tw['start'][2] + $f * ($tw['end'][2] - $tw['start'][2]),
            ];
            // project hit onto triangle plane and check barycentric in triangle AABB inflated
            if ($hit[0] < $tri['mins'][0] - $pad[0] || $hit[0] > $tri['maxs'][0] + $pad[0]
                || $hit[1] < $tri['mins'][1] - $pad[1] || $hit[1] > $tri['maxs'][1] + $pad[1]
                || $hit[2] < $tri['mins'][2] - $pad[2] || $hit[2] > $tri['maxs'][2] + $pad[2]
            ) {
                continue;
            }
            $tw['fraction'] = $f;
            $tw['planeNormal'] = $n;
            $tw['planeDist'] = $tri['d'];
            $tw['contents'] = self::CONTENTS_SOLID;
            if ($tw['fraction'] === 0.0) {
                return;
            }
        }
    }

    // ---- loaders (CMod_Load*) ----

    private function loadShaders(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::SHADER_SIZE);
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::SHADER_SIZE;
            $this->shaders[] = [
                'surfaceFlags' => BspBinary::int32($bytes, $o + 64),
                'contentFlags' => BspBinary::int32($bytes, $o + 68),
            ];
        }
    }

    private function loadPlanes(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::PLANE_SIZE);
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::PLANE_SIZE;
            $nx = BspBinary::float32($bytes, $o);
            $ny = BspBinary::float32($bytes, $o + 4);
            $nz = BspBinary::float32($bytes, $o + 8);
            $dist = BspBinary::float32($bytes, $o + 12);
            $bits = 0;
            if ($nx < 0) {
                $bits |= 1;
            }
            if ($ny < 0) {
                $bits |= 2;
            }
            if ($nz < 0) {
                $bits |= 4;
            }
            // PlaneTypeForNormal macro
            $type = ($nx === 1.0) ? 0 : (($ny === 1.0) ? 1 : (($nz === 1.0) ? 2 : 3));
            $this->planes[] = [
                'normal' => [$nx, $ny, $nz],
                'dist' => $dist,
                'type' => $type,
                'signbits' => $bits,
            ];
        }
    }

    private function loadBrushSides(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::BRUSHSIDE_SIZE);
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::BRUSHSIDE_SIZE;
            $planeNum = BspBinary::int32($bytes, $o);
            $shaderNum = BspBinary::int32($bytes, $o + 4);
            $this->brushsides[] = [
                'plane' => $planeNum,
                'surfaceFlags' => $this->shaders[$shaderNum]['surfaceFlags'],
            ];
        }
    }

    private function loadBrushes(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::BRUSH_SIZE);
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::BRUSH_SIZE;
            $firstSide = BspBinary::int32($bytes, $o);
            $numsides = BspBinary::int32($bytes, $o + 4);
            $shaderNum = BspBinary::int32($bytes, $o + 8);
            $contents = $this->shaders[$shaderNum]['contentFlags'];
            $bounds = [[0.0, 0.0, 0.0], [0.0, 0.0, 0.0]];
            if ($numsides >= 6) {
                // CM_BoundBrush — first 6 sides are axial
                $bounds[0][0] = -$this->planes[$this->brushsides[$firstSide + 0]['plane']]['dist'];
                $bounds[1][0] = $this->planes[$this->brushsides[$firstSide + 1]['plane']]['dist'];
                $bounds[0][1] = -$this->planes[$this->brushsides[$firstSide + 2]['plane']]['dist'];
                $bounds[1][1] = $this->planes[$this->brushsides[$firstSide + 3]['plane']]['dist'];
                $bounds[0][2] = -$this->planes[$this->brushsides[$firstSide + 4]['plane']]['dist'];
                $bounds[1][2] = $this->planes[$this->brushsides[$firstSide + 5]['plane']]['dist'];
            }
            $this->brushes[] = [
                'firstSide' => $firstSide,
                'numsides' => $numsides,
                'contents' => $contents,
                'bounds' => $bounds,
                'checkcount' => 0,
            ];
        }
    }

    private function loadLeafs(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::LEAF_SIZE);
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::LEAF_SIZE;
            $this->leafs[] = [
                'cluster' => BspBinary::int32($bytes, $o),
                'area' => BspBinary::int32($bytes, $o + 4),
                // skip mins/maxs (24 bytes at +8)
                'firstLeafSurface' => BspBinary::int32($bytes, $o + 32),
                'numLeafSurfaces' => BspBinary::int32($bytes, $o + 36),
                'firstLeafBrush' => BspBinary::int32($bytes, $o + 40),
                'numLeafBrushes' => BspBinary::int32($bytes, $o + 44),
            ];
        }
    }

    private function loadLeafBrushes(string $bytes): void
    {
        $count = intdiv(strlen($bytes), 4);
        for ($i = 0; $i < $count; $i++) {
            $this->leafbrushes[] = BspBinary::int32($bytes, $i * 4);
        }
    }

    private function loadNodes(string $bytes): void
    {
        $count = intdiv(strlen($bytes), self::NODE_SIZE);
        $this->numNodes = $count;
        for ($i = 0; $i < $count; $i++) {
            $o = $i * self::NODE_SIZE;
            $this->nodes[] = [
                'plane' => BspBinary::int32($bytes, $o),
                'children' => [
                    BspBinary::int32($bytes, $o + 4),
                    BspBinary::int32($bytes, $o + 8),
                ],
            ];
        }
    }

    // ---- trace internals ----

    /** @param array<string, mixed> $tw */
    private function positionTest(array &$tw): void
    {
        $bounds0 = [
            $tw['start'][0] + $tw['size0'][0] - 1,
            $tw['start'][1] + $tw['size0'][1] - 1,
            $tw['start'][2] + $tw['size0'][2] - 1,
        ];
        $bounds1 = [
            $tw['start'][0] + $tw['size1'][0] + 1,
            $tw['start'][1] + $tw['size1'][1] + 1,
            $tw['start'][2] + $tw['size1'][2] + 1,
        ];

        $this->checkcount++;
        $leafs = $this->boxLeafnums($bounds0, $bounds1);
        $this->checkcount++;

        foreach ($leafs as $leafnum) {
            $this->testInLeaf($tw, $leafnum);
            if ($tw['allSolid']) {
                break;
            }
        }
    }

    /**
     * @param list<float> $mins
     * @param list<float> $maxs
     * @return list<int>
     */
    private function boxLeafnums(array $mins, array $maxs): array
    {
        $list = [];
        $stack = [0];
        while ($stack !== []) {
            $nodenum = array_pop($stack);
            if ($nodenum < 0) {
                $list[] = -1 - $nodenum;
                if (count($list) >= self::MAX_POSITION_LEAFS) {
                    break;
                }
                continue;
            }
            $node = $this->nodes[$nodenum];
            $plane = $this->planes[$node['plane']];
            $s = $this->boxOnPlaneSide($mins, $maxs, $plane);
            if ($s === 1) {
                $stack[] = $node['children'][0];
            } elseif ($s === 2) {
                $stack[] = $node['children'][1];
            } else {
                $stack[] = $node['children'][1];
                $stack[] = $node['children'][0];
            }
        }
        return $list;
    }

    /**
     * @param list<float> $emins
     * @param list<float> $emaxs
     * @param array{normal: list<float>, dist: float, type: int, signbits: int} $p
     */
    private function boxOnPlaneSide(array $emins, array $emaxs, array $p): int
    {
        if ($p['type'] < 3) {
            $t = $p['type'];
            if ($p['dist'] <= $emins[$t]) {
                return 1;
            }
            if ($p['dist'] >= $emaxs[$t]) {
                return 2;
            }
            return 3;
        }

        $n = $p['normal'];
        $corners0 = [
            $n[0] < 0 ? $emins[0] : $emaxs[0],
            $n[1] < 0 ? $emins[1] : $emaxs[1],
            $n[2] < 0 ? $emins[2] : $emaxs[2],
        ];
        $corners1 = [
            $n[0] < 0 ? $emaxs[0] : $emins[0],
            $n[1] < 0 ? $emaxs[1] : $emins[1],
            $n[2] < 0 ? $emaxs[2] : $emins[2],
        ];
        $dist1 = $n[0] * $corners0[0] + $n[1] * $corners0[1] + $n[2] * $corners0[2] - $p['dist'];
        $dist2 = $n[0] * $corners1[0] + $n[1] * $corners1[1] + $n[2] * $corners1[2] - $p['dist'];
        $sides = 0;
        if ($dist1 >= 0) {
            $sides = 1;
        }
        if ($dist2 < 0) {
            $sides |= 2;
        }
        return $sides;
    }

    /**
     * Iterative CM_TraceThroughTree to avoid PHP recursion limits.
     *
     * @param array<string, mixed> $tw
     * @param list<float> $p1
     * @param list<float> $p2
     */
    private function traceThroughTree(array &$tw, int $num, float $p1f, float $p2f, array $p1, array $p2): void
    {
        $stack = [[$num, $p1f, $p2f, $p1, $p2]];

        while ($stack !== []) {
            [$num, $p1f, $p2f, $p1, $p2] = array_pop($stack);

            if ($tw['fraction'] <= $p1f) {
                continue;
            }

            if ($num < 0) {
                $this->traceThroughLeaf($tw, -1 - $num);
                continue;
            }

            $node = $this->nodes[$num];
            $plane = $this->planes[$node['plane']];

            if ($plane['type'] < 3) {
                $t1 = $p1[$plane['type']] - $plane['dist'];
                $t2 = $p2[$plane['type']] - $plane['dist'];
                $offset = $tw['extents'][$plane['type']];
            } else {
                $n = $plane['normal'];
                $t1 = $n[0] * $p1[0] + $n[1] * $p1[1] + $n[2] * $p1[2] - $plane['dist'];
                $t2 = $n[0] * $p2[0] + $n[1] * $p2[1] + $n[2] * $p2[2] - $plane['dist'];
                $offset = $tw['isPoint'] ? 0.0 : 2048.0; // Q3 quirk cm_trace.c:1075
            }

            if ($t1 >= $offset + 1 && $t2 >= $offset + 1) {
                $stack[] = [$node['children'][0], $p1f, $p2f, $p1, $p2];
                continue;
            }
            if ($t1 < -$offset - 1 && $t2 < -$offset - 1) {
                $stack[] = [$node['children'][1], $p1f, $p2f, $p1, $p2];
                continue;
            }

            if ($t1 < $t2) {
                $idist = 1.0 / ($t1 - $t2);
                $side = 1;
                $frac2 = ($t1 + $offset + self::SURFACE_CLIP_EPSILON) * $idist;
                $frac = ($t1 - $offset + self::SURFACE_CLIP_EPSILON) * $idist;
            } elseif ($t1 > $t2) {
                $idist = 1.0 / ($t1 - $t2);
                $side = 0;
                $frac2 = ($t1 - $offset - self::SURFACE_CLIP_EPSILON) * $idist;
                $frac = ($t1 + $offset + self::SURFACE_CLIP_EPSILON) * $idist;
            } else {
                $side = 0;
                $frac = 1.0;
                $frac2 = 0.0;
            }

            $frac = max(0.0, min(1.0, $frac));
            $frac2 = max(0.0, min(1.0, $frac2));

            $midf = $p1f + ($p2f - $p1f) * $frac;
            $mid = [
                $p1[0] + $frac * ($p2[0] - $p1[0]),
                $p1[1] + $frac * ($p2[1] - $p1[1]),
                $p1[2] + $frac * ($p2[2] - $p1[2]),
            ];

            // push far then near so near is processed first (stack LIFO)
            $midf2 = $p1f + ($p2f - $p1f) * $frac2;
            $mid2 = [
                $p1[0] + $frac2 * ($p2[0] - $p1[0]),
                $p1[1] + $frac2 * ($p2[1] - $p1[1]),
                $p1[2] + $frac2 * ($p2[2] - $p1[2]),
            ];
            $stack[] = [$node['children'][$side ^ 1], $midf2, $p2f, $mid2, $p2];
            $stack[] = [$node['children'][$side], $p1f, $midf, $p1, $mid];
        }
    }

    /** @param array<string, mixed> $tw */
    private function traceThroughLeaf(array &$tw, int $leafnum): void
    {
        $leaf = $this->leafs[$leafnum];
        for ($k = 0; $k < $leaf['numLeafBrushes']; $k++) {
            $brushnum = $this->leafbrushes[$leaf['firstLeafBrush'] + $k];
            $b = &$this->brushes[$brushnum];
            if ($b['checkcount'] === $this->checkcount) {
                continue;
            }
            $b['checkcount'] = $this->checkcount;
            if (($b['contents'] & $tw['brushmask']) === 0) {
                continue;
            }
            $this->traceThroughBrush($tw, $brushnum);
            if ($tw['fraction'] === 0.0) {
                return;
            }
        }
        // patches: EXPECTED_NOT_IMPLEMENTED
    }

    /** @param array<string, mixed> $tw */
    private function testInLeaf(array &$tw, int $leafnum): void
    {
        $leaf = $this->leafs[$leafnum];
        for ($k = 0; $k < $leaf['numLeafBrushes']; $k++) {
            $brushnum = $this->leafbrushes[$leaf['firstLeafBrush'] + $k];
            $b = &$this->brushes[$brushnum];
            if ($b['checkcount'] === $this->checkcount) {
                continue;
            }
            $b['checkcount'] = $this->checkcount;
            if (($b['contents'] & $tw['brushmask']) === 0) {
                continue;
            }
            $this->testBoxInBrush($tw, $brushnum);
            if ($tw['allSolid']) {
                return;
            }
        }
    }

    /** @param array<string, mixed> $tw */
    private function testBoxInBrush(array &$tw, int $brushnum): void
    {
        $brush = $this->brushes[$brushnum];
        if ($brush['numsides'] === 0) {
            return;
        }
        $bb = $brush['bounds'];
        if ($tw['bounds0'][0] > $bb[1][0]
            || $tw['bounds0'][1] > $bb[1][1]
            || $tw['bounds0'][2] > $bb[1][2]
            || $tw['bounds1'][0] < $bb[0][0]
            || $tw['bounds1'][1] < $bb[0][1]
            || $tw['bounds1'][2] < $bb[0][2]
        ) {
            return;
        }

        // first six planes axial — remainder only (cm_trace.c:214)
        for ($i = 6; $i < $brush['numsides']; $i++) {
            $side = $this->brushsides[$brush['firstSide'] + $i];
            $plane = $this->planes[$side['plane']];
            $n = $plane['normal'];
            $off = $tw['offsets'][$plane['signbits']];
            $dist = $plane['dist'] - ($off[0] * $n[0] + $off[1] * $n[1] + $off[2] * $n[2]);
            $d1 = $tw['start'][0] * $n[0] + $tw['start'][1] * $n[1] + $tw['start'][2] * $n[2] - $dist;
            if ($d1 > 0) {
                return;
            }
        }

        $tw['startSolid'] = true;
        $tw['allSolid'] = true;
        $tw['fraction'] = 0.0;
        $tw['contents'] = $brush['contents'];
    }

    /** @param array<string, mixed> $tw */
    private function traceThroughBrush(array &$tw, int $brushnum): void
    {
        $brush = $this->brushes[$brushnum];
        if ($brush['numsides'] === 0) {
            return;
        }

        $enterFrac = -1.0;
        $leaveFrac = 1.0;
        $clipplane = null;
        $leadSurfaceFlags = 0;
        $getout = false;
        $startout = false;

        for ($i = 0; $i < $brush['numsides']; $i++) {
            $side = $this->brushsides[$brush['firstSide'] + $i];
            $plane = $this->planes[$side['plane']];
            $n = $plane['normal'];
            $off = $tw['offsets'][$plane['signbits']];
            $dist = $plane['dist'] - ($off[0] * $n[0] + $off[1] * $n[1] + $off[2] * $n[2]);

            $d1 = $tw['start'][0] * $n[0] + $tw['start'][1] * $n[1] + $tw['start'][2] * $n[2] - $dist;
            $d2 = $tw['end'][0] * $n[0] + $tw['end'][1] * $n[1] + $tw['end'][2] * $n[2] - $dist;

            if ($d2 > 0) {
                $getout = true;
            }
            if ($d1 > 0) {
                $startout = true;
            }

            if ($d1 > 0 && ($d2 >= self::SURFACE_CLIP_EPSILON || $d2 >= $d1)) {
                return;
            }
            if ($d1 <= 0 && $d2 <= 0) {
                continue;
            }

            if ($d1 > $d2) {
                $f = ($d1 - self::SURFACE_CLIP_EPSILON) / ($d1 - $d2);
                if ($f < 0) {
                    $f = 0;
                }
                if ($f > $enterFrac) {
                    $enterFrac = $f;
                    $clipplane = $plane;
                    $leadSurfaceFlags = $side['surfaceFlags'];
                }
            } else {
                $f = ($d1 + self::SURFACE_CLIP_EPSILON) / ($d1 - $d2);
                if ($f > 1) {
                    $f = 1;
                }
                if ($f < $leaveFrac) {
                    $leaveFrac = $f;
                }
            }
        }

        if (!$startout) {
            $tw['startSolid'] = true;
            if (!$getout) {
                $tw['allSolid'] = true;
                $tw['fraction'] = 0.0;
                $tw['contents'] = $brush['contents'];
            }
            return;
        }

        if ($enterFrac < $leaveFrac) {
            if ($enterFrac > -1 && $enterFrac < $tw['fraction']) {
                if ($enterFrac < 0) {
                    $enterFrac = 0;
                }
                $tw['fraction'] = $enterFrac;
                if ($clipplane !== null) {
                    $tw['planeNormal'] = $clipplane['normal'];
                    $tw['planeDist'] = $clipplane['dist'];
                }
                $tw['surfaceFlags'] = $leadSurfaceFlags;
                $tw['contents'] = $brush['contents'];
            }
        }
    }
}
