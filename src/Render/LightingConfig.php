<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * Color pipeline knobs from tr_image.c R_SetColorMappings / tr_bsp.c R_ColorShiftLightingBytes.
 *
 * SDL play is windowed: source forces overbrightBits=0 (never overbright in windowed mode).
 * Without hardware gamma, R_LightScaleTexture bakes intensity+gamma into diffuse uploads;
 * lightmaps use ColorShift only, then only_gamma when deviceSupportsGamma is false.
 */
final class LightingConfig
{
    /** @var list<int> 256-entry gamma LUT (includes << overbrightBits) */
    public array $gammaTable = [];

    /** @var list<int> 256-entry intensity LUT */
    public array $intensityTable = [];

    public float $identityLight;
    public int $identityLightByte;
    public int $shift;

    public function __construct(
        public int $mapOverBrightBits = 2,
        /** Windowed default 0 — matches !glConfig.isFullscreen in R_SetColorMappings. */
        public int $overbrightBits = 0,
        public float $gamma = 1.0,
        public float $intensity = 1.0,
        /** When true, apply gamma LUT to uploads (software path when no GLimp_SetGamma). */
        public bool $bakeGammaIntoTextures = true,
        /** Lightmaps are never mipmapped in Q3. */
        public bool $lightmapMipmaps = false,
    ) {
        $this->rebuildTables();
    }

    public function rebuildTables(): void
    {
        if ($this->overbrightBits < 0) {
            $this->overbrightBits = 0;
        }
        if ($this->overbrightBits > 2) {
            $this->overbrightBits = 2;
        }
        $this->identityLight = 1.0 / (1 << $this->overbrightBits);
        $this->identityLightByte = (int) (255 * $this->identityLight);
        $this->shift = $this->mapOverBrightBits - $this->overbrightBits;

        $g = max(0.5, min(3.0, $this->gamma));
        $obShift = $this->overbrightBits;
        $this->gammaTable = [];
        for ($i = 0; $i < 256; $i++) {
            if (abs($g - 1.0) < 1e-6) {
                $inf = $i;
            } else {
                $inf = (int) (255.0 * pow($i / 255.0, 1.0 / $g) + 0.5);
            }
            $inf <<= $obShift;
            $this->gammaTable[$i] = max(0, min(255, $inf));
        }

        $inten = max(1.0, $this->intensity);
        $this->intensityTable = [];
        for ($i = 0; $i < 256; $i++) {
            $this->intensityTable[$i] = min(255, (int) ($i * $inten));
        }
    }

    /**
     * R_ColorShiftLightingBytes (tr_bsp.c) — vertex colors and lightmap texels.
     *
     * @return array{0:int,1:int,2:int,3:int}
     */
    public function colorShiftBytes(int $r, int $g, int $b, int $a = 255): array
    {
        $r <<= $this->shift;
        $g <<= $this->shift;
        $b <<= $this->shift;
        $max = max($r, $g, $b);
        if ($max > 255) {
            $r = (int) ($r * 255 / $max);
            $g = (int) ($g * 255 / $max);
            $b = (int) ($b * 255 / $max);
        }

        return [$r, $g, $b, $a];
    }

    /** Apply ColorShift to packed RGBA8 vertex colors (webgl brightnessAdjustVertex ×4). */
    public function colorShiftBinary(string $rgba): string
    {
        $len = strlen($rgba);
        if ($len < 4 || ($len % 4) !== 0 || $this->shift === 0) {
            return $rgba;
        }
        $out = '';
        for ($i = 0; $i < $len; $i += 4) {
            [$r, $g, $b, $a] = $this->colorShiftBytes(
                ord($rgba[$i]),
                ord($rgba[$i + 1]),
                ord($rgba[$i + 2]),
                ord($rgba[$i + 3]),
            );
            $out .= chr($r) . chr($g) . chr($b) . chr($a);
        }

        return $out;
    }

    /** R_LightScaleTexture subset: intensity then gamma (for diffuse). */
    public function lightScaleRgba(string $rgba): string
    {
        if (!$this->bakeGammaIntoTextures && $this->intensity <= 1.0) {
            return $rgba;
        }
        $out = '';
        $n = strlen($rgba);
        for ($i = 0; $i + 3 < $n; $i += 4) {
            $r = $this->intensityTable[ord($rgba[$i])];
            $g = $this->intensityTable[ord($rgba[$i + 1])];
            $b = $this->intensityTable[ord($rgba[$i + 2])];
            if ($this->bakeGammaIntoTextures) {
                $r = $this->gammaTable[$r];
                $g = $this->gammaTable[$g];
                $b = $this->gammaTable[$b];
            }
            $out .= chr($r) . chr($g) . chr($b) . $rgba[$i + 3];
        }
        return $out;
    }

    /** Apply display gamma to already ColorShift'd lightmap (no intensity). */
    public function gammaRgba(string $rgba): string
    {
        if (!$this->bakeGammaIntoTextures) {
            return $rgba;
        }
        $out = '';
        $n = strlen($rgba);
        for ($i = 0; $i + 3 < $n; $i += 4) {
            $out .= chr($this->gammaTable[ord($rgba[$i])])
                . chr($this->gammaTable[ord($rgba[$i + 1])])
                . chr($this->gammaTable[ord($rgba[$i + 2])])
                . $rgba[$i + 3];
        }
        return $out;
    }

    /** @return array<string, scalar> */
    public function debugInfo(): array
    {
        return [
            'mapOverBrightBits' => $this->mapOverBrightBits,
            'overbrightBits' => $this->overbrightBits,
            'identityLight' => $this->identityLight,
            'identityLightByte' => $this->identityLightByte,
            'lightmapShift' => $this->shift,
            'gamma' => $this->gamma,
            'intensity' => $this->intensity,
            'bakeGammaIntoTextures' => $this->bakeGammaIntoTextures,
            'lightmapMipmaps' => $this->lightmapMipmaps,
        ];
    }

    /** Write RGBA8 page as simple PNG (no deps). */
    public static function writePngRgba(string $path, string $rgba, int $w, int $h): void
    {
        $raw = '';
        $rowBytes = $w * 4;
        for ($y = 0; $y < $h; $y++) {
            $raw .= "\x00" . substr($rgba, $y * $rowBytes, $rowBytes);
        }
        $compressed = gzcompress($raw, 9);
        if ($compressed === false) {
            throw new \RuntimeException('gzcompress failed');
        }
        $ihdr = pack('NNCCCCC', $w, $h, 8, 6, 0, 0, 0);
        $png = "\x89PNG\r\n\x1a\n";
        $png .= self::pngChunk('IHDR', $ihdr);
        $png .= self::pngChunk('IDAT', $compressed);
        $png .= self::pngChunk('IEND', '');
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $png);
    }

    private static function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data) & 0xffffffff);
    }
}
