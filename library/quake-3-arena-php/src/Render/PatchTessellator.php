<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * Bezier patch tessellation (Q3 R_SubdividePatchToGrid / RB_SurfaceGrid).
 * Vertex colors are interpolated from BSP control points like drawVert lerp
 * (webgl-quake3 copies RGB; PHP previously dropped color → white).
 */
final class PatchTessellator
{
    public function __construct(
        private readonly int $level = 5,
    ) {
    }

    /**
     * @param list<array{xyz: list<float>, st: list<float>, lightmap: list<float>, normal?: list<float>, color?: list<int>}> $controls
     * @return array{verts: list<array{xyz: list<float>, st: list<float>, lightmap: list<float>, normal: list<float>, color: list<int>}>, indices: list<int>}
     */
    public function tessellate(array $controls, int $width, int $height): array
    {
        $verts = [];
        $indices = [];
        $L1 = $this->level + 1;

        for ($py = 0; $py < $height - 2; $py += 2) {
            for ($px = 0; $px < $width - 2; $px += 2) {
                $c = [];
                for ($row = 0; $row < 3; $row++) {
                    for ($col = 0; $col < 3; $col++) {
                        $c[] = $controls[($py + $row) * $width + ($px + $col)];
                    }
                }
                $indexOff = count($verts);

                // first column of curve (i=0)
                for ($i = 0; $i < $L1; $i++) {
                    $a = $this->level === 0 ? 0.0 : $i / $this->level;
                    $verts[] = [
                        'xyz' => $this->curve3($c[0]['xyz'], $c[3]['xyz'], $c[6]['xyz'], $a),
                        'st' => $this->curve2($c[0]['st'], $c[3]['st'], $c[6]['st'], $a),
                        'lightmap' => $this->curve2($c[0]['lightmap'], $c[3]['lightmap'], $c[6]['lightmap'], $a),
                        'normal' => $this->normalize($this->curve3(
                            $c[0]['normal'] ?? [0, 0, 1],
                            $c[3]['normal'] ?? [0, 0, 1],
                            $c[6]['normal'] ?? [0, 0, 1],
                            $a,
                        )),
                        'color' => $this->roundColor($this->curveColor(
                            $this->vertColor($c[0]),
                            $this->vertColor($c[3]),
                            $this->vertColor($c[6]),
                            $a,
                        )),
                    ];
                }
                for ($i = 1; $i < $L1; $i++) {
                    $a = $i / $this->level;
                    $pc0 = $this->curve3($c[0]['xyz'], $c[1]['xyz'], $c[2]['xyz'], $a);
                    $pc1 = $this->curve3($c[3]['xyz'], $c[4]['xyz'], $c[5]['xyz'], $a);
                    $pc2 = $this->curve3($c[6]['xyz'], $c[7]['xyz'], $c[8]['xyz'], $a);
                    $tc0 = $this->curve2($c[0]['st'], $c[1]['st'], $c[2]['st'], $a);
                    $tc1 = $this->curve2($c[3]['st'], $c[4]['st'], $c[5]['st'], $a);
                    $tc2 = $this->curve2($c[6]['st'], $c[7]['st'], $c[8]['st'], $a);
                    $lc0 = $this->curve2($c[0]['lightmap'], $c[1]['lightmap'], $c[2]['lightmap'], $a);
                    $lc1 = $this->curve2($c[3]['lightmap'], $c[4]['lightmap'], $c[5]['lightmap'], $a);
                    $lc2 = $this->curve2($c[6]['lightmap'], $c[7]['lightmap'], $c[8]['lightmap'], $a);
                    $nc0 = $this->curve3($c[0]['normal'] ?? [0, 0, 1], $c[1]['normal'] ?? [0, 0, 1], $c[2]['normal'] ?? [0, 0, 1], $a);
                    $nc1 = $this->curve3($c[3]['normal'] ?? [0, 0, 1], $c[4]['normal'] ?? [0, 0, 1], $c[5]['normal'] ?? [0, 0, 1], $a);
                    $nc2 = $this->curve3($c[6]['normal'] ?? [0, 0, 1], $c[7]['normal'] ?? [0, 0, 1], $c[8]['normal'] ?? [0, 0, 1], $a);
                    $cc0 = $this->curveColor($this->vertColor($c[0]), $this->vertColor($c[1]), $this->vertColor($c[2]), $a);
                    $cc1 = $this->curveColor($this->vertColor($c[3]), $this->vertColor($c[4]), $this->vertColor($c[5]), $a);
                    $cc2 = $this->curveColor($this->vertColor($c[6]), $this->vertColor($c[7]), $this->vertColor($c[8]), $a);
                    for ($j = 0; $j < $L1; $j++) {
                        $b = $j / $this->level;
                        $verts[] = [
                            'xyz' => $this->curve3($pc0, $pc1, $pc2, $b),
                            'st' => $this->curve2($tc0, $tc1, $tc2, $b),
                            'lightmap' => $this->curve2($lc0, $lc1, $lc2, $b),
                            'normal' => $this->normalize($this->curve3($nc0, $nc1, $nc2, $b)),
                            'color' => $this->roundColor($this->curveColor($cc0, $cc1, $cc2, $b)),
                        ];
                    }
                }

                // RB_SurfaceGrid: v1=(row,col+1) v2=(row,col) v3=(row+1,col) v4=(row+1,col+1)
                // tris v2,v3,v1 / v1,v3,v4 — winding opposite MakeMeshNormals, matching
                // planar BSP indexes (CW from the interior). webgl-quake3 used the
                // reverse order; that only looked right while PHP culled GL_BACK.
                for ($row = 0; $row < $this->level; $row++) {
                    for ($col = 0; $col < $this->level; $col++) {
                        $v2 = $indexOff + $row * $L1 + $col;
                        $v3 = $indexOff + ($row + 1) * $L1 + $col;
                        $v1 = $indexOff + $row * $L1 + ($col + 1);
                        $v4 = $indexOff + ($row + 1) * $L1 + ($col + 1);
                        $indices[] = $v2;
                        $indices[] = $v3;
                        $indices[] = $v1;
                        $indices[] = $v1;
                        $indices[] = $v3;
                        $indices[] = $v4;
                    }
                }

                // tr_curve.c MakeMeshNormals — BSP control normals are often dummy (0,0,1)
                $this->makeMeshNormals($verts, $indexOff, $L1, $L1);
            }
        }

        // Adjacent Bézier cells duplicate edge verts; average normals at coincident XYZ
        // (Q3 builds one grid + wrap — weld approximates shared-edge continuity).
        $this->weldCoincidentNormals($verts);

        return ['verts' => $verts, 'indices' => $indices];
    }

    /**
     * Average normals for verts that share the same world position (patch cell seams).
     * @param list<array{xyz: list<float>, st: list<float>, lightmap: list<float>, normal: list<float>}> $verts
     */
    private function weldCoincidentNormals(array &$verts): void
    {
        $n = count($verts);
        if ($n < 2) {
            return;
        }
        $eps = 0.001;
        $eps2 = $eps * $eps;
        $inv = 1.0 / $eps;
        /** @var array<string, list<int>> $buckets */
        $buckets = [];
        for ($i = 0; $i < $n; $i++) {
            $p = $verts[$i]['xyz'];
            $key = ((int) round($p[0] * $inv)) . ':'
                . ((int) round($p[1] * $inv)) . ':'
                . ((int) round($p[2] * $inv));
            $buckets[$key][] = $i;
        }
        foreach ($buckets as $idxs) {
            $m = count($idxs);
            if ($m < 2) {
                continue;
            }
            // Cluster by actual distance (hash may collide unrelated verts)
            $used = array_fill(0, $m, false);
            for ($s = 0; $s < $m; $s++) {
                if ($used[$s]) {
                    continue;
                }
                $group = [$idxs[$s]];
                $used[$s] = true;
                $refP = $verts[$idxs[$s]]['xyz'];
                for ($k = $s + 1; $k < $m; $k++) {
                    if ($used[$k]) {
                        continue;
                    }
                    $p = $verts[$idxs[$k]]['xyz'];
                    $dx = $p[0] - $refP[0];
                    $dy = $p[1] - $refP[1];
                    $dz = $p[2] - $refP[2];
                    if ($dx * $dx + $dy * $dy + $dz * $dz <= $eps2) {
                        $group[] = $idxs[$k];
                        $used[$k] = true;
                    }
                }
                if (count($group) < 2) {
                    continue;
                }
                $refN = $verts[$group[0]]['normal'];
                $sum = [0.0, 0.0, 0.0];
                foreach ($group as $i) {
                    $nm = $verts[$i]['normal'];
                    if ($nm[0] * $refN[0] + $nm[1] * $refN[1] + $nm[2] * $refN[2] < 0.0) {
                        $nm = [-$nm[0], -$nm[1], -$nm[2]];
                    }
                    $sum[0] += $nm[0];
                    $sum[1] += $nm[1];
                    $sum[2] += $nm[2];
                }
                $avg = $this->normalize($sum);
                foreach ($group as $i) {
                    $verts[$i]['normal'] = $avg;
                }
            }
        }
    }

    /**
     * MakeMeshNormals subset for a regular L×W tessellated patch grid (tr_curve.c).
     * Includes wrapWidth / wrapHeight when opposite edges coincide.
     * @param list<array{xyz: list<float>, st: list<float>, lightmap: list<float>, normal: list<float>}> $verts
     */
    private function makeMeshNormals(array &$verts, int $indexOff, int $width, int $height): void
    {
        // 8-neighbor offsets (around the vertex), matching tr_curve.c order
        $neighbors = [
            [0, 1], [1, 1], [1, 0], [1, -1],
            [0, -1], [-1, -1], [-1, 0], [-1, 1],
        ];

        $wrapWidth = true;
        for ($i = 0; $i < $height; $i++) {
            $a = $verts[$indexOff + $i * $width]['xyz'];
            $b = $verts[$indexOff + $i * $width + ($width - 1)]['xyz'];
            $dx = $a[0] - $b[0];
            $dy = $a[1] - $b[1];
            $dz = $a[2] - $b[2];
            if ($dx * $dx + $dy * $dy + $dz * $dz > 1.0) {
                $wrapWidth = false;
                break;
            }
        }
        $wrapHeight = true;
        for ($i = 0; $i < $width; $i++) {
            $a = $verts[$indexOff + $i]['xyz'];
            $b = $verts[$indexOff + ($height - 1) * $width + $i]['xyz'];
            $dx = $a[0] - $b[0];
            $dy = $a[1] - $b[1];
            $dz = $a[2] - $b[2];
            if ($dx * $dx + $dy * $dy + $dz * $dz > 1.0) {
                $wrapHeight = false;
                break;
            }
        }

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $baseIdx = $indexOff + $y * $width + $x;
                $base = $verts[$baseIdx]['xyz'];
                $around = [];
                $good = [];
                for ($k = 0; $k < 8; $k++) {
                    $good[$k] = false;
                    $around[$k] = [0.0, 0.0, 0.0];
                    for ($dist = 1; $dist <= 3; $dist++) {
                        $nx = $x + $neighbors[$k][0] * $dist;
                        $ny = $y + $neighbors[$k][1] * $dist;
                        if ($wrapWidth) {
                            if ($nx < 0) {
                                $nx = $width - 1 + $nx;
                            } elseif ($nx >= $width) {
                                $nx = 1 + $nx - $width;
                            }
                        }
                        if ($wrapHeight) {
                            if ($ny < 0) {
                                $ny = $height - 1 + $ny;
                            } elseif ($ny >= $height) {
                                $ny = 1 + $ny - $height;
                            }
                        }
                        if ($nx < 0 || $nx >= $width || $ny < 0 || $ny >= $height) {
                            break;
                        }
                        $other = $verts[$indexOff + $ny * $width + $nx]['xyz'];
                        $temp = [
                            $other[0] - $base[0],
                            $other[1] - $base[1],
                            $other[2] - $base[2],
                        ];
                        $len = sqrt($temp[0] ** 2 + $temp[1] ** 2 + $temp[2] ** 2);
                        if ($len < 1e-8) {
                            continue;
                        }
                        $around[$k] = [$temp[0] / $len, $temp[1] / $len, $temp[2] / $len];
                        $good[$k] = true;
                        break;
                    }
                }
                $sum = [0.0, 0.0, 0.0];
                $count = 0;
                for ($k = 0; $k < 8; $k++) {
                    $k2 = ($k + 1) & 7;
                    if (!$good[$k] || !$good[$k2]) {
                        continue;
                    }
                    $a = $around[$k2];
                    $b = $around[$k];
                    $normal = [
                        $a[1] * $b[2] - $a[2] * $b[1],
                        $a[2] * $b[0] - $a[0] * $b[2],
                        $a[0] * $b[1] - $a[1] * $b[0],
                    ];
                    $nlen = sqrt($normal[0] ** 2 + $normal[1] ** 2 + $normal[2] ** 2);
                    if ($nlen < 1e-8) {
                        continue;
                    }
                    $sum[0] += $normal[0] / $nlen;
                    $sum[1] += $normal[1] / $nlen;
                    $sum[2] += $normal[2] / $nlen;
                    $count++;
                }
                if ($count === 0) {
                    $verts[$baseIdx]['normal'] = [0.0, 0.0, 1.0];
                } else {
                    $verts[$baseIdx]['normal'] = $this->normalize($sum);
                }
            }
        }
    }

    /** @param list<float> $v @return list<float> */
    private function normalize(array $v): array
    {
        $len = sqrt($v[0] ** 2 + $v[1] ** 2 + $v[2] ** 2);
        if ($len < 1e-8) {
            return [0.0, 0.0, 1.0];
        }
        return [$v[0] / $len, $v[1] / $len, $v[2] / $len];
    }

    /** @param list<float> $a @param list<float> $b @param list<float> $c @return list<float> */
    private function curve3(array $a, array $b, array $c, float $t): array
    {
        $b0 = (1 - $t) * (1 - $t);
        $b1 = 2 * (1 - $t) * $t;
        $b2 = $t * $t;
        return [
            $a[0] * $b0 + $b[0] * $b1 + $c[0] * $b2,
            $a[1] * $b0 + $b[1] * $b1 + $c[1] * $b2,
            $a[2] * $b0 + $b[2] * $b1 + $c[2] * $b2,
        ];
    }

    /**
     * @param array{xyz?: list<float>, color?: list<int|float>} $v
     * @return list<float>
     */
    private function vertColor(array $v): array
    {
        $c = $v['color'] ?? [255, 255, 255, 255];

        return [
            (float) ($c[0] ?? 255),
            (float) ($c[1] ?? 255),
            (float) ($c[2] ?? 255),
            (float) ($c[3] ?? 255),
        ];
    }

    /**
     * @param list<float> $a
     * @param list<float> $b
     * @param list<float> $c
     * @return list<float>
     */
    private function curveColor(array $a, array $b, array $c, float $t): array
    {
        $b0 = (1 - $t) * (1 - $t);
        $b1 = 2 * (1 - $t) * $t;
        $b2 = $t * $t;

        return [
            $a[0] * $b0 + $b[0] * $b1 + $c[0] * $b2,
            $a[1] * $b0 + $b[1] * $b1 + $c[1] * $b2,
            $a[2] * $b0 + $b[2] * $b1 + $c[2] * $b2,
            $a[3] * $b0 + $b[3] * $b1 + $c[3] * $b2,
        ];
    }

    /**
     * @param list<float> $c
     * @return list<int>
     */
    private function roundColor(array $c): array
    {
        return [
            (int) max(0, min(255, (int) round($c[0]))),
            (int) max(0, min(255, (int) round($c[1]))),
            (int) max(0, min(255, (int) round($c[2]))),
            (int) max(0, min(255, (int) round($c[3]))),
        ];
    }

    /** @param list<float> $a @param list<float> $b @param list<float> $c @return list<float> */
    private function curve2(array $a, array $b, array $c, float $t): array
    {
        $b0 = (1 - $t) * (1 - $t);
        $b1 = 2 * (1 - $t) * $t;
        $b2 = $t * $t;
        return [
            $a[0] * $b0 + $b[0] * $b1 + $c[0] * $b2,
            $a[1] * $b0 + $b[1] * $b1 + $c[1] * $b2,
        ];
    }
}
