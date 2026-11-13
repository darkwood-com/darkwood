<?php

declare(strict_types=1);

namespace Quake3\Render;

/**
 * TGA (pure PHP) + JPEG (ext-gd) loaders — R_LoadImage subset (.tga then .jpg).
 *
 * Row order matches tr_image.c LoadTGA / LoadJPG (not "GL bottom-left"):
 * - TGA: file rows written into memory from row (h-1) down to 0; top-down bit ignored
 * - JPEG: scanlines top→bottom, no vertical flip
 */
final class ImageLoader
{
    public function loadFromBytes(string $bytes, string $pathHint): DecodedImage
    {
        $lower = strtolower($pathHint);
        if (str_ends_with($lower, '.tga') || $this->looksLikeTga($bytes)) {
            return $this->loadTga($bytes, $pathHint);
        }
        if (str_ends_with($lower, '.jpg') || str_ends_with($lower, '.jpeg')
            || str_starts_with($bytes, "\xFF\xD8")) {
            return $this->loadJpeg($bytes, $pathHint);
        }
        // try TGA then JPEG
        try {
            return $this->loadTga($bytes, $pathHint);
        } catch (\Throwable) {
            return $this->loadJpeg($bytes, $pathHint);
        }
    }

    private function looksLikeTga(string $bytes): bool
    {
        if (strlen($bytes) < 18) {
            return false;
        }
        $type = ord($bytes[2]);
        return $type === 2 || $type === 10 || $type === 3;
    }

    private function loadJpeg(string $bytes, string $path): DecodedImage
    {
        if (!function_exists('imagecreatefromstring')) {
            throw new \RuntimeException('ext-gd required for JPEG');
        }
        $im = @imagecreatefromstring($bytes);
        if ($im === false) {
            throw new \RuntimeException('JPEG decode failed: ' . $path);
        }
        $w = imagesx($im);
        $h = imagesy($im);
        $rgba = '';
        // LoadJPG: scanline 0 = top of buffer (no vertical flip)
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgb = imagecolorat($im, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rgba .= chr($r) . chr($g) . chr($b) . "\xFF";
            }
        }
        unset($im);

        return new DecodedImage($w, $h, $rgba, false, 'jpg', $path);
    }

    private function loadTga(string $bytes, string $path): DecodedImage
    {
        if (strlen($bytes) < 18) {
            throw new \RuntimeException('TGA too short: ' . $path);
        }
        $idLen = ord($bytes[0]);
        $cmapType = ord($bytes[1]);
        $imageType = ord($bytes[2]);
        $w = unpack('v', substr($bytes, 12, 2))[1];
        $h = unpack('v', substr($bytes, 14, 2))[1];
        $bpp = ord($bytes[16]);
        // desc & 0x20 = top-down — Q3 warns and ignores; we match that

        if ($cmapType !== 0) {
            throw new \RuntimeException('TGA colormap not supported: ' . $path);
        }
        if ($w < 1 || $h < 1) {
            throw new \RuntimeException('TGA bad size: ' . $path);
        }

        $offset = 18 + $idLen;
        $hasAlpha = $bpp === 32;
        $pixels = [];

        if ($imageType === 2 || $imageType === 3) {
            // uncompressed
            $bytesPer = intdiv($bpp, 8);
            $need = $w * $h * $bytesPer;
            if (strlen($bytes) < $offset + $need) {
                throw new \RuntimeException('TGA truncated: ' . $path);
            }
            for ($i = 0; $i < $w * $h; $i++) {
                $o = $offset + $i * $bytesPer;
                if ($bpp === 24) {
                    $pixels[] = [$bytes[$o + 2], $bytes[$o + 1], $bytes[$o], "\xFF"];
                } elseif ($bpp === 32) {
                    $pixels[] = [$bytes[$o + 2], $bytes[$o + 1], $bytes[$o], $bytes[$o + 3]];
                } elseif ($bpp === 8) {
                    $g = $bytes[$o];
                    $pixels[] = [$g, $g, $g, "\xFF"];
                } else {
                    throw new \RuntimeException("TGA bpp $bpp unsupported: $path");
                }
            }
        } elseif ($imageType === 10) {
            // RLE truecolor
            $bytesPer = intdiv($bpp, 8);
            $count = 0;
            $pos = $offset;
            while ($count < $w * $h) {
                if ($pos >= strlen($bytes)) {
                    throw new \RuntimeException('TGA RLE truncated: ' . $path);
                }
                $header = ord($bytes[$pos++]);
                $run = ($header & 0x7F) + 1;
                if ($header & 0x80) {
                    $pix = $this->readTgaPixel($bytes, $pos, $bpp);
                    $pos += $bytesPer;
                    for ($i = 0; $i < $run; $i++) {
                        $pixels[] = $pix;
                        $count++;
                    }
                } else {
                    for ($i = 0; $i < $run; $i++) {
                        $pixels[] = $this->readTgaPixel($bytes, $pos, $bpp);
                        $pos += $bytesPer;
                        $count++;
                    }
                }
            }
        } else {
            throw new \RuntimeException("TGA type $imageType unsupported: $path");
        }

        // LoadTGA: sequential file pixels → memory rows (h-1) .. 0
        $rgba = '';
        for ($y = $h - 1; $y >= 0; $y--) {
            for ($x = 0; $x < $w; $x++) {
                $p = $pixels[$y * $w + $x];
                $rgba .= $p[0] . $p[1] . $p[2] . $p[3];
            }
        }

        return new DecodedImage($w, $h, $rgba, $hasAlpha, 'tga', $path);
    }

    /** @return list<string> rgba chars */
    private function readTgaPixel(string $bytes, int $pos, int $bpp): array
    {
        if ($bpp === 24) {
            return [$bytes[$pos + 2], $bytes[$pos + 1], $bytes[$pos], "\xFF"];
        }
        if ($bpp === 32) {
            return [$bytes[$pos + 2], $bytes[$pos + 1], $bytes[$pos], $bytes[$pos + 3]];
        }
        if ($bpp === 8) {
            $g = $bytes[$pos];
            return [$g, $g, $g, "\xFF"];
        }
        throw new \RuntimeException("TGA bpp $bpp");
    }
}
