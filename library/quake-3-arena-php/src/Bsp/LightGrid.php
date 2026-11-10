<?php

declare(strict_types=1);

namespace Quake3\Bsp;

use Quake3\Game\EntityParser;

/**
 * LUMP_LIGHTGRID — R_LoadLightGrid / R_SetupEntityLightingGrid (tr_bsp.c / tr_light.c).
 *
 * Each sample is 8 bytes: ambient RGB, directed RGB, lat/lng direction bytes.
 */
final class LightGrid
{
    public const FUNCTABLE_SIZE = 1024;

    /** @var list<float> */
    public readonly array $size;
    /** @var list<float> */
    public readonly array $origin;
    /** @var list<float> */
    public readonly array $inverseSize;
    /** @var list<int> */
    public readonly array $bounds;
    public readonly string $data;
    public readonly bool $valid;

    /**
     * @param list<float> $size
     * @param list<float> $origin
     * @param list<int> $bounds
     */
    public function __construct(
        array $size,
        array $origin,
        array $bounds,
        string $data,
        bool $valid,
    ) {
        $this->size = $size;
        $this->origin = $origin;
        $this->inverseSize = [
            1.0 / $size[0],
            1.0 / $size[1],
            1.0 / $size[2],
        ];
        $this->bounds = $bounds;
        $this->data = $data;
        $this->valid = $valid;
    }

    public static function load(BspMap $map, int $mapOverBrightBits = 2, int $overbrightBits = 0): self
    {
        $size = [64.0, 64.0, 128.0];
        $ents = (new EntityParser())->parseEntities($map->readLumpBytes(BspIdent::LUMP_ENTITIES));
        if ($ents !== [] && isset($ents[0]['gridsize'])) {
            $p = preg_split('/\s+/', trim($ents[0]['gridsize'])) ?: [];
            if (count($p) >= 3) {
                $size = [(float) $p[0], (float) $p[1], (float) $p[2]];
            }
        }

        $models = $map->readLumpBytes(BspIdent::LUMP_MODELS);
        if (strlen($models) < 24) {
            return new self($size, [0.0, 0.0, 0.0], [0, 0, 0], '', false);
        }
        $mins = [
            BspBinary::float32($models, 0),
            BspBinary::float32($models, 4),
            BspBinary::float32($models, 8),
        ];
        $maxs = [
            BspBinary::float32($models, 12),
            BspBinary::float32($models, 16),
            BspBinary::float32($models, 20),
        ];

        $origin = [0.0, 0.0, 0.0];
        $bounds = [0, 0, 0];
        for ($i = 0; $i < 3; $i++) {
            $origin[$i] = $size[$i] * ceil($mins[$i] / $size[$i]);
            $mx = $size[$i] * floor($maxs[$i] / $size[$i]);
            $bounds[$i] = (int) (($mx - $origin[$i]) / $size[$i] + 1);
        }

        $raw = $map->readLumpBytes(BspIdent::LUMP_LIGHTGRID);
        $numPoints = $bounds[0] * $bounds[1] * $bounds[2];
        if ($numPoints <= 0 || strlen($raw) !== $numPoints * 8) {
            return new self($size, $origin, $bounds, '', false);
        }

        $shift = $mapOverBrightBits - $overbrightBits;
        $data = '';
        for ($i = 0; $i < $numPoints; $i++) {
            $o = $i * 8;
            $data .= self::colorShift3($raw, $o, $shift);
            $data .= self::colorShift3($raw, $o + 3, $shift);
            $data .= $raw[$o + 6] . $raw[$o + 7];
        }

        return new self($size, $origin, $bounds, $data, true);
    }

    private static function colorShift3(string $raw, int $o, int $shift): string
    {
        $r = ord($raw[$o]) << $shift;
        $g = ord($raw[$o + 1]) << $shift;
        $b = ord($raw[$o + 2]) << $shift;
        $max = max($r, $g, $b);
        if ($max > 255) {
            $r = (int) ($r * 255 / $max);
            $g = (int) ($g * 255 / $max);
            $b = (int) ($b * 255 / $max);
        }

        return chr($r) . chr($g) . chr($b);
    }

    /**
     * R_SetupEntityLightingGrid sample at world point.
     *
     * @return array{
     *   ambient: list<float>,
     *   directed: list<float>,
     *   lightDir: list<float>
     * }|null  RGB in 0–255 style floats; lightDir normalized world-space
     */
    public function sample(array $point): ?array
    {
        if (!$this->valid || $this->data === '') {
            return null;
        }

        $pos = [0, 0, 0];
        $frac = [0.0, 0.0, 0.0];
        for ($i = 0; $i < 3; $i++) {
            $v = ($point[$i] - $this->origin[$i]) * $this->inverseSize[$i];
            $pos[$i] = (int) floor($v);
            $frac[$i] = $v - $pos[$i];
            if ($pos[$i] < 0) {
                $pos[$i] = 0;
            } elseif ($pos[$i] >= $this->bounds[$i] - 1) {
                $pos[$i] = $this->bounds[$i] - 1;
            }
        }

        $gridStep = [
            8,
            8 * $this->bounds[0],
            8 * $this->bounds[0] * $this->bounds[1],
        ];
        $base = $pos[0] * $gridStep[0] + $pos[1] * $gridStep[1] + $pos[2] * $gridStep[2];

        $ambient = [0.0, 0.0, 0.0];
        $directed = [0.0, 0.0, 0.0];
        $direction = [0.0, 0.0, 0.0];
        $totalFactor = 0.0;
        $dataLen = strlen($this->data);

        for ($i = 0; $i < 8; $i++) {
            $factor = 1.0;
            $off = $base;
            for ($j = 0; $j < 3; $j++) {
                if ($i & (1 << $j)) {
                    $factor *= $frac[$j];
                    $off += $gridStep[$j];
                } else {
                    $factor *= (1.0 - $frac[$j]);
                }
            }
            if ($off < 0 || $off + 7 >= $dataLen) {
                continue;
            }
            $a0 = ord($this->data[$off]);
            $a1 = ord($this->data[$off + 1]);
            $a2 = ord($this->data[$off + 2]);
            if ($a0 + $a1 + $a2 === 0) {
                continue; // wall sample
            }
            $totalFactor += $factor;
            $ambient[0] += $factor * $a0;
            $ambient[1] += $factor * $a1;
            $ambient[2] += $factor * $a2;
            $directed[0] += $factor * ord($this->data[$off + 3]);
            $directed[1] += $factor * ord($this->data[$off + 4]);
            $directed[2] += $factor * ord($this->data[$off + 5]);

            $lng = ord($this->data[$off + 6]) * (self::FUNCTABLE_SIZE / 256);
            $lat = ord($this->data[$off + 7]) * (self::FUNCTABLE_SIZE / 256);
            $normal = $this->latLngNormal((int) $lat, (int) $lng);
            $direction[0] += $factor * $normal[0];
            $direction[1] += $factor * $normal[1];
            $direction[2] += $factor * $normal[2];
        }

        if ($totalFactor > 0.0 && $totalFactor < 0.99) {
            $scale = 1.0 / $totalFactor;
            $ambient[0] *= $scale;
            $ambient[1] *= $scale;
            $ambient[2] *= $scale;
            $directed[0] *= $scale;
            $directed[1] *= $scale;
            $directed[2] *= $scale;
        }

        // r_ambientScale 0.6 / r_directedScale 1.0 (tr_init.c defaults)
        $ambient[0] *= 0.6;
        $ambient[1] *= 0.6;
        $ambient[2] *= 0.6;

        $len = sqrt($direction[0] ** 2 + $direction[1] ** 2 + $direction[2] ** 2);
        if ($len > 1e-6) {
            $lightDir = [$direction[0] / $len, $direction[1] / $len, $direction[2] / $len];
        } else {
            $lightDir = [0.0, 0.0, 1.0];
        }

        return [
            'ambient' => $ambient,
            'directed' => $directed,
            'lightDir' => $lightDir,
        ];
    }

    /** @return list<float> */
    private function latLngNormal(int $lat, int $lng): array
    {
        $twopi = 2.0 * M_PI / self::FUNCTABLE_SIZE;
        $lat &= self::FUNCTABLE_SIZE - 1;
        $lng &= self::FUNCTABLE_SIZE - 1;
        $sinLat = sin($lat * $twopi);
        $cosLat = sin((($lat + self::FUNCTABLE_SIZE / 4) & (self::FUNCTABLE_SIZE - 1)) * $twopi);
        $sinLng = sin($lng * $twopi);
        $cosLng = sin((($lng + self::FUNCTABLE_SIZE / 4) & (self::FUNCTABLE_SIZE - 1)) * $twopi);

        return [
            $cosLat * $sinLng,
            $sinLat * $sinLng,
            $cosLng,
        ];
    }
}
