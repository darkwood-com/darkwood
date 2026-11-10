<?php

declare(strict_types=1);

namespace Quake3\Bsp;

/**
 * LUMP_LIGHTMAPS — 128×128 RGB pages (tr_bsp.c R_LoadLightmaps).
 */
final class LightmapLump
{
    public const PAGE_SIZE = 128;
    public const BYTES_PER_PAGE = 128 * 128 * 3;

    /** @param list<string> $pagesRgb raw RGB24 per page */
    public function __construct(
        public readonly array $pagesRgb,
    ) {
    }

    public static function load(BspMap $map): self
    {
        $bytes = $map->readLumpBytes(BspIdent::LUMP_LIGHTMAPS);
        $len = strlen($bytes);
        if ($len === 0) {
            return new self([]);
        }
        if ($len % self::BYTES_PER_PAGE !== 0) {
            throw new \RuntimeException('Lightmap lump size not multiple of page');
        }
        $count = intdiv($len, self::BYTES_PER_PAGE);
        $pages = [];
        for ($i = 0; $i < $count; $i++) {
            $pages[] = substr($bytes, $i * self::BYTES_PER_PAGE, self::BYTES_PER_PAGE);
        }
        return new self($pages);
    }

    public function count(): int
    {
        return count($this->pagesRgb);
    }

    /**
     * RGB24 → RGBA8 with R_ColorShiftLightingBytes (windowed: mapOB=2, overbrightBits=0 → shift 2).
     *
     * Upload order matches tr_bsp.c R_LoadLightmaps / webgl-quake3: linear j=0..(128²-1),
     * no vertical flip. BSP lightmap UVs assume first texel = (0,0); flipping inverted sampling.
     */
    public function pageRgba(int $index, int $mapOverBrightBits = 2, int $overbrightBits = 0): string
    {
        $rgb = $this->pagesRgb[$index];
        $rgba = '';
        $shift = $mapOverBrightBits - $overbrightBits;
        $n = self::PAGE_SIZE * self::PAGE_SIZE;
        for ($j = 0; $j < $n; $j++) {
            $o = $j * 3;
            $r = ord($rgb[$o]) << $shift;
            $g = ord($rgb[$o + 1]) << $shift;
            $b = ord($rgb[$o + 2]) << $shift;
            $max = max($r, $g, $b);
            if ($max > 255) {
                // normalize by color instead of saturating (source)
                $r = (int) ($r * 255 / $max);
                $g = (int) ($g * 255 / $max);
                $b = (int) ($b * 255 / $max);
            }
            $rgba .= chr($r) . chr($g) . chr($b) . "\xFF";
        }
        return $rgba;
    }

    /** Mean RGB of a shifted page (diagnostics). @return array{0:float,1:float,2:float} */
    public function pageMeanRgb(int $index, int $mapOverBrightBits = 2, int $overbrightBits = 0): array
    {
        $rgba = $this->pageRgba($index, $mapOverBrightBits, $overbrightBits);
        $n = intdiv(strlen($rgba), 4);
        $sr = 0;
        $sg = 0;
        $sb = 0;
        for ($i = 0; $i < $n; $i++) {
            $o = $i * 4;
            $sr += ord($rgba[$o]);
            $sg += ord($rgba[$o + 1]);
            $sb += ord($rgba[$o + 2]);
        }
        return [$sr / max(1, $n), $sg / max(1, $n), $sb / max(1, $n)];
    }
}
