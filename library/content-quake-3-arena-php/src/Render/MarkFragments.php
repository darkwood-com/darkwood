<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * R_MarkFragments / R_ChopPolyBehindPlane / R_AddMarkFragments subset.
 * Projects a mark polygon onto world planar (and tessellated patch) triangles.
 */
final class MarkFragments
{
    public const MAX_VERTS_ON_POLY = 64;
    public const MAX_MARK_FRAGMENTS = 128;
    public const MAX_MARK_POINTS = 384;

    private const SIDE_FRONT = 0;
    private const SIDE_BACK = 1;
    private const SIDE_ON = 2;

    public const SURF_SKY = 0x4;
    public const SURF_NOIMPACT = 0x10;
    public const SURF_NOMARKS = 0x20;
    /** ParseMesh only — keep for clip, skip draw (surfaceflags.h). */
    public const SURF_NODRAW = 0x80;
    public const CONTENTS_FOG = 64;

    /**
     * @param list<list<float>> $points mark polygon (typically 4)
     * @param list<float> $projection world offset applied to points (CG: dir * -20)
     * @param list<array{0:list<float>,1:list<float>,2:list<float>,normal:list<float>}> $tris
     * @return list<list<list<float>>> fragments as lists of xyz
     */
    public static function project(array $points, array $projection, array $tris): array
    {
        $numPoints = count($points);
        if ($numPoints < 3 || $tris === []) {
            return [];
        }
        if ($numPoints > self::MAX_VERTS_ON_POLY) {
            $numPoints = self::MAX_VERTS_ON_POLY;
            $points = array_slice($points, 0, $numPoints);
        }

        $projLen = sqrt($projection[0] ** 2 + $projection[1] ** 2 + $projection[2] ** 2) ?: 1.0;
        $projectionDir = [
            $projection[0] / $projLen,
            $projection[1] / $projLen,
            $projection[2] / $projLen,
        ];

        $mins = [INF, INF, INF];
        $maxs = [-INF, -INF, -INF];
        for ($i = 0; $i < $numPoints; $i++) {
            self::addPointBounds($points[$i], $mins, $maxs);
            self::addPointBounds([
                $points[$i][0] + $projection[0],
                $points[$i][1] + $projection[1],
                $points[$i][2] + $projection[2],
            ], $mins, $maxs);
            self::addPointBounds([
                $points[$i][0] - 20.0 * $projectionDir[0],
                $points[$i][1] - 20.0 * $projectionDir[1],
                $points[$i][2] - 20.0 * $projectionDir[2],
            ], $mins, $maxs);
        }

        /** @var list<list<float>> $normals */
        $normals = [];
        /** @var list<float> $dists */
        $dists = [];
        for ($i = 0; $i < $numPoints; $i++) {
            $a = $points[$i];
            $b = $points[($i + 1) % $numPoints];
            $v1 = [$b[0] - $a[0], $b[1] - $a[1], $b[2] - $a[2]];
            // points[i] - (points[i]+projection) = -projection (tr_marks.c)
            $v2 = [-$projection[0], -$projection[1], -$projection[2]];
            // Cross(v2, v1) so normals face into the mark volume (chop keeps FRONT)
            $n = self::cross($v2, $v1);
            $n = self::normalize($n);
            $normals[] = $n;
            $dists[] = $n[0] * $a[0] + $n[1] * $a[1] + $n[2] * $a[2];
        }
        // near / far clip planes along projection
        $normals[] = $projectionDir;
        $dists[] = ($projectionDir[0] * $points[0][0] + $projectionDir[1] * $points[0][1] + $projectionDir[2] * $points[0][2]) - 32.0;
        $normals[] = [-$projectionDir[0], -$projectionDir[1], -$projectionDir[2]];
        $dists[] = (-$projectionDir[0] * $points[0][0] - $projectionDir[1] * $points[0][1] - $projectionDir[2] * $points[0][2]) - 20.0;
        $numPlanes = count($normals);

        $fragments = [];
        $returnedPoints = 0;

        foreach ($tris as $tri) {
            $n = $tri['normal'];
            $ndot = $n[0] * $projectionDir[0] + $n[1] * $projectionDir[1] + $n[2] * $projectionDir[2];
            if ($ndot > -0.5) {
                continue;
            }
            // AABB reject
            $tmin = [
                min($tri[0][0], $tri[1][0], $tri[2][0]),
                min($tri[0][1], $tri[1][1], $tri[2][1]),
                min($tri[0][2], $tri[1][2], $tri[2][2]),
            ];
            $tmax = [
                max($tri[0][0], $tri[1][0], $tri[2][0]),
                max($tri[0][1], $tri[1][1], $tri[2][1]),
                max($tri[0][2], $tri[1][2], $tri[2][2]),
            ];
            if ($tmax[0] < $mins[0] || $tmin[0] > $maxs[0]
                || $tmax[1] < $mins[1] || $tmin[1] > $maxs[1]
                || $tmax[2] < $mins[2] || $tmin[2] > $maxs[2]) {
                continue;
            }

            $clip = [
                [
                    [$tri[0][0], $tri[0][1], $tri[0][2]],
                    [$tri[1][0], $tri[1][1], $tri[1][2]],
                    [$tri[2][0], $tri[2][1], $tri[2][2]],
                ],
                [],
            ];
            $numClip = 3;
            $ping = 0;
            for ($p = 0; $p < $numPlanes; $p++) {
                $numClip = self::chopPolyBehindPlane(
                    $numClip,
                    $clip[$ping],
                    $clip[1 - $ping],
                    $normals[$p],
                    $dists[$p],
                    0.5,
                );
                $ping = 1 - $ping;
                if ($numClip === 0) {
                    break;
                }
            }
            if ($numClip === 0) {
                continue;
            }
            if ($returnedPoints + $numClip > self::MAX_MARK_POINTS) {
                break;
            }
            $frag = [];
            for ($i = 0; $i < $numClip; $i++) {
                $frag[] = $clip[$ping][$i];
            }
            $fragments[] = $frag;
            $returnedPoints += $numClip;
            if (count($fragments) >= self::MAX_MARK_FRAGMENTS) {
                break;
            }
        }

        return $fragments;
    }

    /**
     * @param list<list<float>> $inPoints
     * @param list<list<float>> $outPoints
     * @param list<float> $normal
     */
    public static function chopPolyBehindPlane(
        int $numInPoints,
        array $inPoints,
        array &$outPoints,
        array $normal,
        float $dist,
        float $epsilon,
    ): int {
        $outPoints = [];
        if ($numInPoints >= self::MAX_VERTS_ON_POLY - 2) {
            return 0;
        }

        $dists = [];
        $sides = [];
        $counts = [0, 0, 0];
        for ($i = 0; $i < $numInPoints; $i++) {
            $dot = $inPoints[$i][0] * $normal[0] + $inPoints[$i][1] * $normal[1] + $inPoints[$i][2] * $normal[2] - $dist;
            $dists[$i] = $dot;
            if ($dot > $epsilon) {
                $sides[$i] = self::SIDE_FRONT;
            } elseif ($dot < -$epsilon) {
                $sides[$i] = self::SIDE_BACK;
            } else {
                $sides[$i] = self::SIDE_ON;
            }
            $counts[$sides[$i]]++;
        }
        $sides[$numInPoints] = $sides[0];
        $dists[$numInPoints] = $dists[0];

        if ($counts[self::SIDE_FRONT] === 0) {
            return 0;
        }
        if ($counts[self::SIDE_BACK] === 0) {
            for ($i = 0; $i < $numInPoints; $i++) {
                $outPoints[] = $inPoints[$i];
            }
            return $numInPoints;
        }

        for ($i = 0; $i < $numInPoints; $i++) {
            $p1 = $inPoints[$i];
            if ($sides[$i] === self::SIDE_ON) {
                $outPoints[] = $p1;
                continue;
            }
            if ($sides[$i] === self::SIDE_FRONT) {
                $outPoints[] = $p1;
            }
            if ($sides[$i + 1] === self::SIDE_ON || $sides[$i + 1] === $sides[$i]) {
                continue;
            }
            $p2 = $inPoints[($i + 1) % $numInPoints];
            $d = $dists[$i] - $dists[$i + 1];
            $frac = $d == 0.0 ? 0.0 : $dists[$i] / $d;
            $outPoints[] = [
                $p1[0] + $frac * ($p2[0] - $p1[0]),
                $p1[1] + $frac * ($p2[1] - $p1[1]),
                $p1[2] + $frac * ($p2[2] - $p1[2]),
            ];
        }

        return count($outPoints);
    }

    /** @param list<float> $p @param list<float> $mins @param list<float> $maxs */
    private static function addPointBounds(array $p, array &$mins, array &$maxs): void
    {
        for ($a = 0; $a < 3; $a++) {
            $mins[$a] = min($mins[$a], $p[$a]);
            $maxs[$a] = max($maxs[$a], $p[$a]);
        }
    }

    /** @param list<float> $a @param list<float> $b @return list<float> */
    private static function cross(array $a, array $b): array
    {
        return [
            $a[1] * $b[2] - $a[2] * $b[1],
            $a[2] * $b[0] - $a[0] * $b[2],
            $a[0] * $b[1] - $a[1] * $b[0],
        ];
    }

    /** @param list<float> $v @return list<float> */
    private static function normalize(array $v): array
    {
        $len = sqrt($v[0] ** 2 + $v[1] ** 2 + $v[2] ** 2) ?: 1.0;
        return [$v[0] / $len, $v[1] / $len, $v[2] / $len];
    }
}
