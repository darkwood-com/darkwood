<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

/**
 * CPU RGBA compositor for --software-compose A/B vs SDL.
 * Transparent mask uses 50% RGB average (BlitPixelsBlended stand-in).
 */
final class SoftwareFramebuffer
{
    public string $rgba;
    public int $w;
    public int $h;

    public function __construct(int $w, int $h)
    {
        $this->w = $w;
        $this->h = $h;
        $this->rgba = str_repeat("\x00\x00\x00\xFF", $w * $h);
    }

    public function clear(int $r, int $g, int $b, int $a = 255): void
    {
        $px = chr($r) . chr($g) . chr($b) . chr($a);
        $this->rgba = str_repeat($px, $this->w * $this->h);
    }

    /** Opaque fill (clipped). Used for OOB black tiles. */
    public function fillRect(int $x, int $y, int $w, int $h, int $r = 0, int $g = 0, int $b = 0, int $a = 255): void
    {
        if ($w <= 0 || $h <= 0) {
            return;
        }
        $px = chr($r) . chr($g) . chr($b) . chr($a);
        $dst = $this->rgba;
        for ($row = 0; $row < $h; $row++) {
            $gy = $y + $row;
            if ($gy < 0 || $gy >= $this->h) {
                continue;
            }
            for ($col = 0; $col < $w; $col++) {
                $gx = $x + $col;
                if ($gx < 0 || $gx >= $this->w) {
                    continue;
                }
                $di = ($gy * $this->w + $gx) * 4;
                $dst[$di] = $px[0];
                $dst[$di + 1] = $px[1];
                $dst[$di + 2] = $px[2];
                $dst[$di + 3] = $px[3];
            }
        }
        $this->rgba = $dst;
    }

    /**
     * @return array{written:int,skippedAlpha0:int,skippedMask:int,opaqueBlack:int}
     */
    public function blit(string $src, int $sw, int $sh, int $dx, int $dy, bool $blend50 = false): array
    {
        $written = 0;
        $skipA = 0;
        $skipM = 0;
        $black = 0;
        $fw = $this->w;
        $fh = $this->h;
        $dst = $this->rgba;
        for ($y = 0; $y < $sh; $y++) {
            $gy = $dy + $y;
            if ($gy < 0 || $gy >= $fh) {
                continue;
            }
            for ($x = 0; $x < $sw; $x++) {
                $gx = $dx + $x;
                if ($gx < 0 || $gx >= $fw) {
                    continue;
                }
                $si = ($y * $sw + $x) * 4;
                $sa = ord($src[$si + 3]);
                if ($sa === 0) {
                    $skipA++;
                    continue;
                }
                $di = ($gy * $fw + $gx) * 4;
                $sr = ord($src[$si]);
                $sg = ord($src[$si + 1]);
                $sb = ord($src[$si + 2]);
                if ($blend50 || $sa < 255) {
                    // BlitPixelsBlended stand-in: average with destination
                    $dr = ord($dst[$di]);
                    $dg = ord($dst[$di + 1]);
                    $db = ord($dst[$di + 2]);
                    $dst[$di] = chr(($sr + $dr) >> 1);
                    $dst[$di + 1] = chr(($sg + $dg) >> 1);
                    $dst[$di + 2] = chr(($sb + $db) >> 1);
                    $dst[$di + 3] = "\xFF";
                } else {
                    $dst[$di] = chr($sr);
                    $dst[$di + 1] = chr($sg);
                    $dst[$di + 2] = chr($sb);
                    $dst[$di + 3] = "\xFF";
                }
                $written++;
                if ($sr + $sg + $sb === 0) {
                    $black++;
                }
            }
        }
        $this->rgba = $dst;

        return [
            'written' => $written,
            'skippedAlpha0' => $skipA,
            'skippedMask' => $skipM,
            'opaqueBlack' => $black,
        ];
    }

    public function toPng(string $path): void
    {
        if (!function_exists('imagecreatetruecolor')) {
            return;
        }
        $im = imagecreatetruecolor($this->w, $this->h);
        $raw = $this->rgba;
        for ($y = 0; $y < $this->h; $y++) {
            for ($x = 0; $x < $this->w; $x++) {
                $o = ($y * $this->w + $x) * 4;
                $c = imagecolorallocate($im, ord($raw[$o]), ord($raw[$o + 1]), ord($raw[$o + 2]));
                imagesetpixel($im, $x, $y, $c);
            }
        }
        $dir = dirname($path);
        if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        imagepng($im, $path);
    }
}
