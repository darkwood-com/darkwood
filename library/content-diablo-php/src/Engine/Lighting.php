<?php

declare(strict_types=1);

namespace Diablo\Engine;

use Diablo\Engine\Assets\Palette;
use Diablo\Levels\DungeonMap;

/**
 * dLight + LightTables (MakeLightTable).
 * Apply shade by remapping palette indices, not RGB multiply.
 */
final class Lighting
{
    public const LIGHTS_MAX = 15;
    public const NUM_LEVELS = 16;
    /** NumLightRadiuses — LightFalloffs rows (radius index 0..15). */
    public const NUM_LIGHT_RADIUSES = 16;

    /** @var list<array{x:int,y:int,radius:int,ox:int,oy:int}> */
    private array $lights = [];

    /** @var list<list<int>> LightTables[light][colorIndex] → remapped index */
    private static ?array $lightTables = null;

    /** @var list<list<int>>|null LightFalloffs[radius][linearDistance 0..127] */
    private static ?array $lightFalloffs = null;

    /**
     * LightConeInterpolations[offsetX][offsetY][x][y] — 32×32 circle samples at 1/8 tile.
     * @var list<list<list<list<int>>>>|null
     */
    private static ?array $lightCone = null;

    public function clear(): void
    {
        $this->lights = [];
    }

    public function addLight(int $x, int $y, int $radius, int $offsetX = 0, int $offsetY = 0): void
    {
        $this->lights[] = [
            'x' => $x,
            'y' => $y,
            'radius' => max(0, min(self::NUM_LIGHT_RADIUSES - 1, $radius)),
            'ox' => $offsetX,
            'oy' => $offsetY,
        ];
    }

    /** Build 16 shade tables — MakeLightTable band remaps. */
    public static function makeLightTables(): void
    {
        if (self::$lightTables !== null) {
            return;
        }
        self::$lightTables = [];
        $stepsList = [16, 16, 16, 16, 16, 16, 16, 16, 8, 8, 8, 8, 16, 16, 16, 16, 16, 16];
        for ($shade = 0; $shade < self::NUM_LEVELS; $shade++) {
            $table = array_fill(0, 256, 0);
            $colorIndex = 0;
            foreach ($stepsList as $steps) {
                $shading = intdiv($shade * $steps, 16);
                $shadeStart = $colorIndex;
                $shadeEnd = $shadeStart + $steps - 1;
                for ($step = 0; $step < $steps; $step++) {
                    if ($colorIndex === 0) {
                        $table[$colorIndex++] = 0;
                        continue;
                    }
                    $color = $shadeStart + $step + $shading;
                    if ($color > $shadeEnd || $color === 255) {
                        $color = 0;
                    }
                    $table[$colorIndex++] = $color;
                }
            }
            while ($colorIndex < 256) {
                $table[$colorIndex++] = 0;
            }
            self::$lightTables[$shade] = $table;
        }
        self::$lightTables[15] = array_fill(0, 256, 0);
        self::makeLightFalloffs();
        self::makeLightConeInterpolations();
    }

    /** Leaner falloff ranges — MakeLightTable LightFalloffs (non-Hellfire nest/crypt). */
    private static function makeLightFalloffs(): void
    {
        self::$lightFalloffs = [];
        $maxDarkness = 15.0;
        for ($radius = 0; $radius < self::NUM_LIGHT_RADIUSES; $radius++) {
            $maxDistance = ($radius + 1) * 8;
            $row = [];
            for ($distance = 0; $distance < 128; $distance++) {
                if ($distance > $maxDistance) {
                    $row[$distance] = 15;
                } else {
                    $factor = $maxDistance > 0 ? ($distance / $maxDistance) : 1.0;
                    $scaled = $factor * $maxDarkness + 0.5; // round up
                    $row[$distance] = (int) min(15, max(0, (int) $scaled));
                }
            }
            self::$lightFalloffs[$radius] = $row;
        }
    }

    /** LightConeInterpolations — MakeLightTable. */
    private static function makeLightConeInterpolations(): void
    {
        self::$lightCone = [];
        for ($offsetY = 0; $offsetY < 8; $offsetY++) {
            self::$lightCone[$offsetY] = [];
            for ($offsetX = 0; $offsetX < 8; $offsetX++) {
                self::$lightCone[$offsetY][$offsetX] = [];
                for ($y = 0; $y < 16; $y++) {
                    self::$lightCone[$offsetY][$offsetX][$y] = [];
                    for ($x = 0; $x < 16; $x++) {
                        $a = (8 * $x) - $offsetX;
                        $b = (8 * $y) - $offsetY;
                        self::$lightCone[$offsetY][$offsetX][$y][$x] = (int) sqrt(($a * $a) + ($b * $b));
                    }
                }
            }
        }
    }

    /** @return list<int> */
    public static function table(int $lightTableIndex): array
    {
        self::makeLightTables();

        return self::$lightTables[max(0, min(15, $lightTableIndex))] ?? self::$lightTables[0];
    }

    public function processLightList(DungeonMap $map): void
    {
        $default = $map->levelType === DungeonMap::DTYPE_TOWN ? 0 : self::LIGHTS_MAX;
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $map->dLight[$y][$x] = $default;
            }
        }
        if ($map->levelType === DungeonMap::DTYPE_TOWN) {
            return;
        }
        foreach ($this->lights as $L) {
            $this->doLighting($map, $L['x'], $L['y'], $L['radius'], $L['ox'], $L['oy']);
        }
    }

    /**
     * DoLighting — quadrant LightConeInterpolations + LightFalloffs.
     * $offsetX/$offsetY are sub-tile walk offsets in 0..7 (negative folds into previous tile).
     */
    public function doLighting(DungeonMap $map, int $px, int $py, int $radius, int $offsetX = 0, int $offsetY = 0): void
    {
        if (!$map->inBounds($px, $py)) {
            return;
        }
        self::makeLightTables();
        $r = max(0, min(self::NUM_LIGHT_RADIUSES - 1, $radius));
        $falloffs = self::$lightFalloffs[$r] ?? [];
        $cone = self::$lightCone;
        if ($cone === null) {
            return;
        }

        if ($offsetX < 0) {
            $offsetX += 8;
            $px -= 1;
        }
        if ($offsetY < 0) {
            $offsetY += 8;
            $py -= 1;
        }
        $offsetX = max(0, min(7, $offsetX));
        $offsetY = max(0, min(7, $offsetY));

        if (!$map->inBounds($px, $py)) {
            return;
        }

        $distX = $offsetX;
        $distY = $offsetY;
        $lightX = 0;
        $lightY = 0;
        $blockX = 0;
        $blockY = 0;

        $minX = 15;
        if ($px - 15 < 0) {
            $minX = $px + 1;
        }
        $maxX = 15;
        if ($px + 15 > DungeonMap::MAXDUNX) {
            $maxX = DungeonMap::MAXDUNX - $px;
        }
        $minY = 15;
        if ($py - 15 < 0) {
            $minY = $py + 1;
        }
        $maxY = 15;
        if ($py + 15 > DungeonMap::MAXDUNY) {
            $maxY = DungeonMap::MAXDUNY - $py;
        }

        $map->dLight[$py][$px] = 0;

        for ($i = 0; $i < 4; $i++) {
            $yBound = ($i > 0 && $i < 3) ? $maxY : $minY;
            $xBound = ($i < 2) ? $maxX : $minX;
            for ($y = 0; $y < $yBound; $y++) {
                for ($x = 1; $x < $xBound; $x++) {
                    $ix = $x + $blockX;
                    $iy = $y + $blockY;
                    if ($ix < 0 || $ix >= 16 || $iy < 0 || $iy >= 16) {
                        continue;
                    }
                    $linearDistance = $cone[$offsetY][$offsetX][$iy][$ix] ?? 128;
                    if ($linearDistance >= 128) {
                        continue;
                    }
                    [$rdx, $rdy] = self::rotateDisplacement($x, $y, -$i);
                    $tx = $px + $rdx;
                    $ty = $py + $rdy;
                    if (!$map->inBounds($tx, $ty)) {
                        continue;
                    }
                    $v = (int) ($falloffs[$linearDistance] ?? 15);
                    if ($v < ($map->dLight[$ty][$tx] ?? 15)) {
                        $map->dLight[$ty][$tx] = $v;
                    }
                }
            }
            // RotateRadius
            $ndX = 7 - $distY;
            $ndY = $distX;
            $nlX = 7 - $lightY;
            $nlY = $lightX;
            $distX = $ndX;
            $distY = $ndY;
            $lightX = $nlX;
            $lightY = $nlY;
            $offsetX = $distX - $lightX;
            $offsetY = $distY - $lightY;
            $blockX = 0;
            if ($offsetX < 0) {
                $offsetX += 8;
                $blockX = 1;
            }
            $blockY = 0;
            if ($offsetY < 0) {
                $offsetY += 8;
                $blockY = 1;
            }
        }
    }

    /** Displacement::Rotate(quadrants) — Sines {0,1,0,-1}. */
    private static function rotateDisplacement(int $dx, int $dy, int $quadrants): array
    {
        $quadrants = (($quadrants % 4) + 4) % 4;
        $sines = [0, 1, 0, -1];
        $sine = $sines[$quadrants];
        $cosine = $sines[($quadrants + 1) % 4];

        return [$dx * $cosine - $dy * $sine, $dx * $sine + $dy * $cosine];
    }

    /**
     * Apply LightTables shade to already-decoded RGBA by darkening toward black
     * proportional to table (until full indexed re-blit is wired everywhere).
     * Prefer applyLightToIndexed when decoding from palette.
     */
    public static function applyLightToRgba(string $rgba, int $w, int $h, int $lightTableIndex): string
    {
        if ($lightTableIndex <= 0) {
            return $rgba;
        }
        if ($lightTableIndex >= 15) {
            $out = $rgba;
            $n = $w * $h;
            for ($i = 0; $i < $n; $i++) {
                $o = $i * 4;
                if (ord($out[$o + 3]) === 0) {
                    continue;
                }
                $out[$o] = "\0";
                $out[$o + 1] = "\0";
                $out[$o + 2] = "\0";
            }

            return $out;
        }
        $shade = 1.0 - ($lightTableIndex / 15.0) * 0.85;
        $out = $rgba;
        $n = $w * $h;
        for ($i = 0; $i < $n; $i++) {
            $o = $i * 4;
            if (ord($out[$o + 3]) === 0) {
                continue;
            }
            $out[$o] = chr((int) (ord($out[$o]) * $shade));
            $out[$o + 1] = chr((int) (ord($out[$o + 1]) * $shade));
            $out[$o + 2] = chr((int) (ord($out[$o + 2]) * $shade));
        }

        return $out;
    }

    /**
     * Decode palette index through LightTables then to RGB.
     * @return array{0:int,1:int,2:int}
     */
    public static function shadedRgb(Palette $pal, int $colorIndex, int $lightTableIndex): array
    {
        if ($colorIndex === 0) {
            return [0, 0, 0];
        }
        $table = self::table($lightTableIndex);
        $mapped = $table[$colorIndex] ?? $colorIndex;

        return $pal->rgb($mapped);
    }
}
