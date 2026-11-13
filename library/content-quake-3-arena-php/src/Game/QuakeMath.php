<?php

declare(strict_types=1);

namespace Quake3\Game;

/**
 * Quake AngleVectors / AnglesToAxis (q_math.c) for view-weapon placement.
 */
final class QuakeMath
{
    /**
     * @param list<float> $angles pitch,yaw,roll degrees
     * @return array{0: list<float>, 1: list<float>, 2: list<float>} forward, right, up
     */
    public static function angleVectors(array $angles): array
    {
        $pitch = deg2rad($angles[0]);
        $yaw = deg2rad($angles[1]);
        $roll = deg2rad($angles[2] ?? 0.0);

        $sp = sin($pitch);
        $cp = cos($pitch);
        $sy = sin($yaw);
        $cy = cos($yaw);
        $sr = sin($roll);
        $cr = cos($roll);

        $forward = [$cp * $cy, $cp * $sy, -$sp];
        $right = [
            -1 * $sr * $sp * $cy + -1 * $cr * -$sy,
            -1 * $sr * $sp * $sy + -1 * $cr * $cy,
            -1 * $sr * $cp,
        ];
        $up = [
            $cr * $sp * $cy + -$sr * -$sy,
            $cr * $sp * $sy + -$sr * $cy,
            $cr * $cp,
        ];
        return [$forward, $right, $up];
    }

    /**
     * AnglesToAxis: axis[0]=forward, axis[1]=-right, axis[2]=up.
     * @param list<float> $angles
     * @return list<list<float>> 3×3
     */
    public static function anglesToAxis(array $angles): array
    {
        [$forward, $right, $up] = self::angleVectors($angles);
        return [
            $forward,
            [-$right[0], -$right[1], -$right[2]],
            $up,
        ];
    }

    /**
     * CG_PositionEntityOnTag: entity.origin = parent.origin + tag.origin·parent.axis
     * entity.axis = tag.axis × parent.axis
     *
     * @param list<float> $parentOrigin
     * @param list<list<float>> $parentAxis
     * @param array{origin: list<float>, axis: list<list<float>>} $tag
     * @return array{origin: list<float>, axis: list<list<float>>}
     */
    public static function positionOnTag(array $parentOrigin, array $parentAxis, array $tag): array
    {
        $origin = $parentOrigin;
        for ($i = 0; $i < 3; $i++) {
            $origin[0] += $tag['origin'][$i] * $parentAxis[$i][0];
            $origin[1] += $tag['origin'][$i] * $parentAxis[$i][1];
            $origin[2] += $tag['origin'][$i] * $parentAxis[$i][2];
        }
        $axis = self::matrixMultiply($tag['axis'], $parentAxis);
        return ['origin' => $origin, 'axis' => $axis];
    }

    /**
     * CG_PositionRotatedEntityOnTag: origin same as OnTag;
     * axis = entity.axis × tag.axis × parent.axis
     *
     * @param list<float> $parentOrigin
     * @param list<list<float>> $parentAxis
     * @param array{origin: list<float>, axis: list<list<float>>} $tag
     * @param list<list<float>> $entityAxis local entity axis before tag
     * @return array{origin: list<float>, axis: list<list<float>>}
     */
    public static function positionRotatedOnTag(
        array $parentOrigin,
        array $parentAxis,
        array $tag,
        array $entityAxis,
    ): array {
        $origin = $parentOrigin;
        for ($i = 0; $i < 3; $i++) {
            $origin[0] += $tag['origin'][$i] * $parentAxis[$i][0];
            $origin[1] += $tag['origin'][$i] * $parentAxis[$i][1];
            $origin[2] += $tag['origin'][$i] * $parentAxis[$i][2];
        }
        $temp = self::matrixMultiply($entityAxis, $tag['axis']);
        $axis = self::matrixMultiply($temp, $parentAxis);
        return ['origin' => $origin, 'axis' => $axis];
    }

    /**
     * MatrixMultiply(in1, in2, out) — row vectors as in q_math.c.
     * @param list<list<float>> $a @param list<list<float>> $b
     * @return list<list<float>>
     */
    public static function matrixMultiply(array $a, array $b): array
    {
        $out = [];
        for ($i = 0; $i < 3; $i++) {
            $out[$i] = [
                $a[$i][0] * $b[0][0] + $a[$i][1] * $b[1][0] + $a[$i][2] * $b[2][0],
                $a[$i][0] * $b[0][1] + $a[$i][1] * $b[1][1] + $a[$i][2] * $b[2][1],
                $a[$i][0] * $b[0][2] + $a[$i][1] * $b[1][2] + $a[$i][2] * $b[2][2],
            ];
        }
        return $out;
    }

    /**
     * Transform local point by origin+axis.
     * @param list<float> $local @param list<float> $origin @param list<list<float>> $axis
     * @return list<float>
     */
    public static function transformPoint(array $local, array $origin, array $axis): array
    {
        return [
            $origin[0] + $local[0] * $axis[0][0] + $local[1] * $axis[1][0] + $local[2] * $axis[2][0],
            $origin[1] + $local[0] * $axis[0][1] + $local[1] * $axis[1][1] + $local[2] * $axis[2][1],
            $origin[2] + $local[0] * $axis[0][2] + $local[1] * $axis[1][2] + $local[2] * $axis[2][2],
        ];
    }
}
