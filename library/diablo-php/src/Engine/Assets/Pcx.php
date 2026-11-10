<?php

declare(strict_types=1);

namespace Diablo\Engine\Assets;

/**
 * 8-bit PCX decode (Diablo ui_art). See NOTICE.md.
 */
final class Pcx
{
    /**
     * @param list<int> $transparentIndices palette indices forced to alpha=0 (e.g. 250 UI chroma, 0 font)
     * @return array{w:int,h:int,rgba:string,palette:Palette,indices:string}
     */
    public static function decode(string $data, array $transparentIndices = []): array
    {
        if (strlen($data) < 128) {
            throw new \RuntimeException('PCX too short');
        }
        $xmin = unpack('v', substr($data, 4, 2))[1];
        $ymin = unpack('v', substr($data, 6, 2))[1];
        $xmax = unpack('v', substr($data, 8, 2))[1];
        $ymax = unpack('v', substr($data, 10, 2))[1];
        $bpp = ord($data[3]);
        $nPlanes = ord($data[65]);
        $bytesPerLine = unpack('v', substr($data, 66, 2))[1];
        $w = $xmax - $xmin + 1;
        $h = $ymax - $ymin + 1;
        if ($bpp !== 8 || $nPlanes !== 1) {
            throw new \RuntimeException("Unsupported PCX bpp={$bpp} planes={$nPlanes}");
        }

        $pal = new Palette();
        $palOff = strlen($data) - 769;
        if ($palOff > 128 && ord($data[$palOff]) === 12) {
            $colors = [];
            for ($i = 0; $i < 256; $i++) {
                $o = $palOff + 1 + $i * 3;
                $colors[$i] = [ord($data[$o]), ord($data[$o + 1]), ord($data[$o + 2])];
            }
            $pal->colors = $colors;
        } else {
            $pal->colors = array_fill(0, 256, [0, 0, 0]);
        }

        $trans = [];
        foreach ($transparentIndices as $ti) {
            $trans[(int) $ti] = true;
        }

        $pos = 128;
        $end = $palOff > 128 ? $palOff : strlen($data);
        $indices = '';
        $lineBytes = $bytesPerLine;
        for ($y = 0; $y < $h; $y++) {
            $x = 0;
            while ($x < $lineBytes && $pos < $end) {
                $b = ord($data[$pos++]);
                if (($b & 0xC0) === 0xC0) {
                    $count = $b & 0x3F;
                    if ($pos >= $end) {
                        break;
                    }
                    $val = ord($data[$pos++]);
                    for ($i = 0; $i < $count && $x < $lineBytes; $i++, $x++) {
                        if ($x < $w) {
                            $indices .= chr($val);
                        }
                    }
                } else {
                    if ($x < $w) {
                        $indices .= chr($b);
                    }
                    $x++;
                }
            }
            while ($x < $w) {
                $indices .= "\0";
                $x++;
            }
        }

        $rgba = '';
        $n = min(strlen($indices), $w * $h);
        for ($i = 0; $i < $n; $i++) {
            $idx = ord($indices[$i]);
            if (isset($trans[$idx])) {
                $rgba .= "\x00\x00\x00\x00";
                continue;
            }
            [$r, $g, $b] = $pal->rgb($idx);
            $rgba .= chr($r) . chr($g) . chr($b) . "\xFF";
        }
        while (strlen($rgba) < $w * $h * 4) {
            $rgba .= "\x00\x00\x00\x00";
        }

        return ['w' => $w, 'h' => $h, 'rgba' => $rgba, 'palette' => $pal, 'indices' => $indices];
    }

    /**
     * Vertical PCX sprite list (DevilutionX LoadPcxSpriteList / PcxToClx).
     * Sheet height is split evenly: frameH = h / numFrames.
     *
     * @param list<int> $transparentIndices
     * @return array{w:int,frameH:int,numFrames:int,sheetH:int,frames:list<string>,palette:Palette}
     */
    public static function decodeSpriteList(string $data, int $numFrames, array $transparentIndices = []): array
    {
        $numFrames = max(1, $numFrames);
        $sheet = self::decode($data, $transparentIndices);
        $w = $sheet['w'];
        $h = $sheet['h'];
        $frameH = intdiv($h, $numFrames);
        if ($frameH < 1) {
            throw new \RuntimeException("PCX sprite list: height {$h} < numFrames {$numFrames}");
        }
        $frames = [];
        $rowBytes = $w * 4;
        for ($fi = 0; $fi < $numFrames; $fi++) {
            $off = $fi * $frameH * $rowBytes;
            $need = $frameH * $rowBytes;
            $chunk = substr($sheet['rgba'], $off, $need);
            if (strlen($chunk) < $need) {
                $chunk = str_pad($chunk, $need, "\0");
            }
            $frames[] = $chunk;
        }

        return [
            'w' => $w,
            'frameH' => $frameH,
            'numFrames' => $numFrames,
            'sheetH' => $h,
            'frames' => $frames,
            'palette' => $sheet['palette'],
        ];
    }
}
