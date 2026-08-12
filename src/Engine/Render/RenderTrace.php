<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

/**
 * Frame draw-trace for composition parity (not CEL decode).
 * Enable via RenderTrace::begin() before drawGame; write JSON after.
 */
final class RenderTrace
{
    public static bool $enabled = false;
    public static bool $softwareCompose = false;
    /** @var array{0:int,1:int}|null */
    public static ?array $debugPixel = null;

    /** @var list<array<string,mixed>> */
    private static array $draws = [];
    private static int $seq = 0;
    private static string $layer = '';

    /** @var SoftwareFramebuffer|null */
    public static ?SoftwareFramebuffer $fb = null;

    public static function begin(bool $softwareCompose = false, int $w = 640, int $h = 480): void
    {
        self::$enabled = true;
        self::$softwareCompose = $softwareCompose;
        self::$draws = [];
        self::$seq = 0;
        self::$layer = '';
        self::$fb = null;
        if ($softwareCompose) {
            self::$fb = new SoftwareFramebuffer($w, $h);
            self::$fb->clear(0, 0, 0, 255);
        }
    }

    public static function end(): void
    {
        self::$enabled = false;
    }

    public static function setLayer(string $layer): void
    {
        self::$layer = $layer;
    }

    public static function layer(): string
    {
        return self::$layer;
    }

    /**
     * @param array<string,mixed> $rec
     */
    public static function record(array $rec): int
    {
        if (!self::$enabled) {
            return -1;
        }
        $rec['seq'] = ++self::$seq;
        if (!isset($rec['layer'])) {
            $rec['layer'] = self::$layer;
        }
        self::$draws[] = $rec;

        return (int) $rec['seq'];
    }

    /** @return list<array<string,mixed>> */
    public static function draws(): array
    {
        return self::$draws;
    }

    /**
     * Draws whose dst rect contains (px,py), in order, plus winner (last with opaque sample).
     * @return array{pixel:array{0:int,1:int},stack:list<array<string,mixed>>,winner:?array<string,mixed>}
     */
    public static function stackAt(int $px, int $py, ?callable $sampleOpaque = null): array
    {
        $stack = [];
        $winner = null;
        foreach (self::$draws as $d) {
            $sx = (int) ($d['sx'] ?? -9999);
            $sy = (int) ($d['sy'] ?? -9999);
            $w = (int) ($d['w'] ?? 0);
            $h = (int) ($d['h'] ?? 0);
            if ($px < $sx || $py < $sy || $px >= $sx + $w || $py >= $sy + $h) {
                continue;
            }
            $lx = $px - $sx;
            $ly = $py - $sy;
            $entry = $d + ['localX' => $lx, 'localY' => $ly];
            $opaque = true;
            if ($sampleOpaque !== null) {
                $opaque = (bool) $sampleOpaque($d, $lx, $ly);
            } elseif (isset($d['rgba']) && is_string($d['rgba'])) {
                $o = ($ly * $w + $lx) * 4;
                $rgba = $d['rgba'];
                $opaque = isset($rgba[$o + 3]) && ord($rgba[$o + 3]) > 0;
                if ($opaque) {
                    $entry['sampleRgb'] = [ord($rgba[$o]), ord($rgba[$o + 1]), ord($rgba[$o + 2])];
                    $entry['sampleA'] = ord($rgba[$o + 3]);
                }
            }
            $entry['opaqueAtPixel'] = $opaque ? 1 : 0;
            $stack[] = $entry;
            if ($opaque) {
                $winner = $entry;
            }
        }

        return ['pixel' => [$px, $py], 'stack' => $stack, 'winner' => $winner];
    }

    public static function writeJson(string $path, array $extra = []): void
    {
        $dir = dirname($path);
        if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        // Strip bulky rgba from file unless keepRgba
        $draws = [];
        foreach (self::$draws as $d) {
            $copy = $d;
            unset($copy['rgba']);
            $draws[] = $copy;
        }
        $payload = array_merge([
            'drawCount' => count($draws),
            'softwareCompose' => self::$softwareCompose ? 1 : 0,
            'debugPixel' => self::$debugPixel,
            'draws' => $draws,
        ], $extra);
        file_put_contents($path, json_encode($payload, JSON_UNESCAPED_SLASHES) . "\n");
    }

    /**
     * Heatmaps from draw stats (rect coverage counts).
     * @return array{written:string,skipped:string}|null paths
     */
    public static function writeHeatmaps(string $dir, int $w = 640, int $h = 352): ?array
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null;
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        $written = array_fill(0, $w * $h, 0);
        $skipped = array_fill(0, $w * $h, 0);
        foreach (self::$draws as $d) {
            $sx = (int) ($d['sx'] ?? 0);
            $sy = (int) ($d['sy'] ?? 0);
            $dw = (int) ($d['w'] ?? 0);
            $dh = (int) ($d['h'] ?? 0);
            $pw = (int) ($d['pixelsWritten'] ?? 0);
            $ps = (int) ($d['pixelsSkippedAlpha0'] ?? 0) + (int) ($d['pixelsSkippedMask'] ?? 0);
            // Approximate: distribute counts across rect
            $area = max(1, $dw * $dh);
            for ($y = 0; $y < $dh; $y++) {
                for ($x = 0; $x < $dw; $x++) {
                    $gx = $sx + $x;
                    $gy = $sy + $y;
                    if ($gx < 0 || $gy < 0 || $gx >= $w || $gy >= $h) {
                        continue;
                    }
                    $i = $gy * $w + $gx;
                    $written[$i] += intdiv($pw, $area) > 0 ? 1 : ($pw > 0 ? 1 : 0);
                    $skipped[$i] += intdiv($ps, $area) > 0 ? 1 : ($ps > 0 ? 1 : 0);
                }
            }
        }
        $pathW = $dir . '/overdraw-written.png';
        $pathS = $dir . '/overdraw-skipped.png';
        self::heatmapPng($pathW, $written, $w, $h, false);
        self::heatmapPng($pathS, $skipped, $w, $h, true);

        return ['written' => $pathW, 'skipped' => $pathS];
    }

    /** @param list<int> $counts */
    private static function heatmapPng(string $path, array $counts, int $w, int $h, bool $blue): void
    {
        $max = max(1, max($counts));
        $im = imagecreatetruecolor($w, $h);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $v = $counts[$y * $w + $x];
                $t = (int) round(255 * $v / $max);
                $c = $blue
                    ? imagecolorallocate($im, 0, $t >> 1, $t)
                    : imagecolorallocate($im, $t, $t >> 2, 0);
                imagesetpixel($im, $x, $y, $c);
            }
        }
        imagepng($im, $path);
    }
}
