<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * Simple fly camera — TEMPORARY_SIMPLIFICATION (not Q3 pmove).
 * Quake Z-up: X forward-right plane, Z up.
 */
final class FlyCamera
{
    public float $x;
    public float $y;
    public float $z;
    public float $yaw = 0.0;   // radians around Z
    public float $pitch = 0.0; // radians look up/down

    public function __construct(float $x = 0.0, float $y = 0.0, float $z = 64.0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }

    /** m_yaw * sensitivity defaults → radians/px (0.022 × 5 deg). */
    public const LOOK_SENS_RAD = 0.022 * 5.0 * M_PI / 180.0;

    public function look(float $dx, float $dy, ?float $sensitivity = null): void
    {
        $sensitivity ??= self::LOOK_SENS_RAD;
        $this->yaw -= $dx * $sensitivity;
        $this->pitch -= $dy * $sensitivity;
        $max = M_PI / 2 - 0.05;
        $this->pitch = max(-$max, min($max, $this->pitch));
    }

    /**
     * @param array<string, bool> $keys
     */
    public function move(array $keys, float $dt, float $speed = 320.0): void
    {
        $forward = 0.0;
        $right = 0.0;
        $up = 0.0;
        if (!empty($keys['w']) || !empty($keys['up'])) {
            $forward += 1;
        }
        if (!empty($keys['s']) || !empty($keys['down'])) {
            $forward -= 1;
        }
        if (!empty($keys['d']) || !empty($keys['right'])) {
            $right += 1;
        }
        if (!empty($keys['a']) || !empty($keys['left'])) {
            $right -= 1;
        }
        if (!empty($keys['space'])) {
            $up += 1;
        }
        if (!empty($keys['shift'])) {
            $up -= 1;
        }

        $cp = cos($this->pitch);
        $sp = sin($this->pitch);
        $cy = cos($this->yaw);
        $sy = sin($this->yaw);

        // forward in XY plane with pitch affecting Z
        $fx = $cp * $cy;
        $fy = $cp * $sy;
        $fz = $sp;

        $rx = -$sy;
        $ry = $cy;
        $rz = 0.0;

        $len = sqrt($forward * $forward + $right * $right + $up * $up);
        if ($len > 0) {
            $forward /= $len;
            $right /= $len;
            $up /= $len;
        }

        $dist = $speed * $dt;
        $this->x += ($fx * $forward + $rx * $right) * $dist;
        $this->y += ($fy * $forward + $ry * $right) * $dist;
        $this->z += ($fz * $forward + $rz * $right + $up) * $dist;
    }

    /** @return list<float> column-major 4x4 projection */
    public function projectionMatrix(float $aspect, float $fovyDeg = 70.0, float $near = 1.0, float $far = 10000.0): array
    {
        $f = 1.0 / tan(deg2rad($fovyDeg) / 2.0);
        $nf = 1.0 / ($near - $far);

        return [
            $f / $aspect, 0, 0, 0,
            0, $f, 0, 0,
            0, 0, ($far + $near) * $nf, -1,
            0, 0, (2 * $far * $near) * $nf, 0,
        ];
    }

    /**
     * CG_CalcFov — cg_fov is horizontal; derive vertical for OpenGL fovy.
     * fov_y = atan(tan(fov_x/2) / (width/height)) * 2
     */
    public static function verticalFovFromHorizontal(float $fovXDeg, float $aspect): float
    {
        $aspect = max(0.0001, $aspect);

        return rad2deg(atan(tan(deg2rad($fovXDeg) / 2.0) / $aspect)) * 2.0;
    }

    /** @return list<float> column-major view matrix */
    public function viewMatrix(): array
    {
        $cp = cos($this->pitch);
        $sp = sin($this->pitch);
        $cy = cos($this->yaw);
        $sy = sin($this->yaw);

        $fx = $cp * $cy;
        $fy = $cp * $sy;
        $fz = $sp;

        // up vector approx world Z tilted
        $ux = -$sp * $cy;
        $uy = -$sp * $sy;
        $uz = $cp;

        $rx = $fy * $uz - $fz * $uy;
        $ry = $fz * $ux - $fx * $uz;
        $rz = $fx * $uy - $fy * $ux;
        $rlen = sqrt($rx * $rx + $ry * $ry + $rz * $rz) ?: 1.0;
        $rx /= $rlen;
        $ry /= $rlen;
        $rz /= $rlen;

        // re-orthogonalize up
        $ux = $ry * $fz - $rz * $fy;
        $uy = $rz * $fx - $rx * $fz;
        $uz = $rx * $fy - $ry * $fx;

        $ex = $this->x;
        $ey = $this->y;
        $ez = $this->z;

        return [
            $rx, $ux, -$fx, 0,
            $ry, $uy, -$fy, 0,
            $rz, $uz, -$fz, 0,
            -($rx * $ex + $ry * $ey + $rz * $ez),
            -($ux * $ex + $uy * $ey + $uz * $ez),
            -(-$fx * $ex - $fy * $ey - $fz * $ez),
            1,
        ];
    }
}
