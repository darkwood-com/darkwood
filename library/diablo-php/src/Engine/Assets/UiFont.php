<?php

declare(strict_types=1);

namespace Diablo\Engine\Assets;

/**
 * Classic DiabloUI artfont (font42 PCX + .bin).
 *
 * PCX chroma key + classic Storm artfont vertical strip.
 * DIABDAT ui_art\font42*.pcx: glyph-cell fill = palette index 32 (RGB 0,255,0) — NOT index 0.
 * font42.bin: [0]=maxWidth-ish, [1]=height, [2..257]=per-char widths.
 */
final class UiFont
{
    /** Classic artfont green cell fill (measured on DIABDAT font42g.pcx). */
    public const CHROMA_INDEX = 32;

    private string $indices = '';
    private Palette $palette;
    private int $sheetW = 0;
    private int $sheetH = 0;
    /** @var list<int> */
    private array $widths = [];
    private int $glyphH = 42;
    private int $chroma = self::CHROMA_INDEX;

    public static function load42(AssetStore $assets, bool $gold = true): self
    {
        return self::load($assets, 42, $gold ? 'g' : 'y');
    }

    public static function load16(AssetStore $assets, bool $gold = true): self
    {
        return self::load($assets, 16, $gold ? 'g' : 'y');
    }

    /** FontSize24 + ColorUiSilver → classic artfont font24s. */
    public static function load24(AssetStore $assets, string $variant = 's'): self
    {
        return self::load($assets, 24, $variant);
    }

    /**
     * Classic artfont PCX + .bin (font{16,24,30,42}).
     * Variant: g=gold, y=yellow, s=silver.
     */
    public static function load(AssetStore $assets, int $size, bool|string $goldOrVariant = true): self
    {
        $size = in_array($size, [16, 24, 30, 42], true) ? $size : 42;
        $f = new self();
        if (is_string($goldOrVariant)) {
            $suffix = $goldOrVariant;
        } else {
            $suffix = $goldOrVariant ? 'g' : 'y';
        }
        if (!in_array($suffix, ['g', 'y', 's'], true)) {
            $suffix = 'g';
        }
        $pcxPath = "ui_art\\font{$size}{$suffix}.pcx";
        if (!$assets->has($pcxPath)) {
            $fallbacks = match ($suffix) {
                's' => ["ui_art\\font{$size}g.pcx", 'ui_art\\font42g.pcx'],
                'g' => ["ui_art\\font{$size}y.pcx", 'ui_art\\font42g.pcx'],
                'y' => ["ui_art\\font{$size}g.pcx", 'ui_art\\font42g.pcx'],
                default => ['ui_art\\font42g.pcx'],
            };
            $found = null;
            foreach ($fallbacks as $alt) {
                if ($assets->has($alt)) {
                    $found = $alt;
                    break;
                }
            }
            if ($found === null) {
                throw new \RuntimeException("UiFont: missing {$pcxPath}");
            }
            if ($found === 'ui_art\\font42g.pcx' && $size !== 42) {
                fwrite(STDERR, "UiFont: missing {$pcxPath}, falling back to font42g\n");

                return self::load($assets, 42, 'g');
            }
            if ($found !== $pcxPath) {
                fwrite(STDERR, "UiFont: missing {$pcxPath}, using {$found}\n");
                $pcxPath = $found;
            }
        }
        // Indexed decode; chroma 32 → alpha 0 (do not RGB-mask green)
        $pcx = Pcx::decode($assets->read($pcxPath), [$f->chroma]);
        $f->indices = $pcx['indices'];
        $f->palette = $pcx['palette'];
        $f->sheetW = $pcx['w'];
        $f->sheetH = $pcx['h'];

        $binPath = "ui_art\\font{$size}.bin";
        if (!$assets->has($binPath)) {
            throw new \RuntimeException("UiFont: missing {$binPath}");
        }
        $bin = $assets->read($binPath);
        if (strlen($bin) < 3) {
            throw new \RuntimeException("UiFont: font{$size}.bin too short");
        }
        $f->glyphH = max(1, ord($bin[1]));
        for ($i = 0; $i < 256; $i++) {
            $off = 2 + $i;
            $f->widths[$i] = $off < strlen($bin) ? ord($bin[$off]) : 0;
        }
        if (($f->widths[32] ?? 0) === 0) {
            $f->widths[32] = max(4, intdiv($f->glyphH, 3));
        }
        if ($f->sheetH < $f->glyphH) {
            throw new \RuntimeException("UiFont: sheet height {$f->sheetH} < glyphH {$f->glyphH}");
        }

        return $f;
    }

    public function glyphHeight(): int
    {
        return $this->glyphH;
    }

    /**
     * Pipeline debug for one glyph (-style: index → chroma → RGB).
     * @return array{char:string,code:int,hist:array<int,int>,after_chroma_opaque:int,green_opaque:int,sample_rgb:list<array{0:int,1:int,2:int}>}
     */
    public function debugGlyphPipeline(string $char): array
    {
        $c = ord($char[0] ?? 'A');
        $gw = min($this->widths[$c] ?? 0, $this->sheetW);
        $hist = [];
        $opaque = 0;
        $green = 0;
        $samples = [];
        $sy = $c * $this->glyphH;
        for ($y = 0; $y < $this->glyphH; $y++) {
            for ($x = 0; $x < $gw; $x++) {
                $srcY = $sy + $y;
                if ($srcY >= $this->sheetH) {
                    break 2;
                }
                $idx = ord($this->indices[$srcY * $this->sheetW + $x] ?? "\0");
                $hist[$idx] = ($hist[$idx] ?? 0) + 1;
                if ($idx === $this->chroma) {
                    continue;
                }
                $opaque++;
                [$r, $g, $b] = $this->palette->rgb($idx);
                if ($r === 0 && $g === 255 && $b === 0) {
                    $green++;
                }
                if (count($samples) < 8) {
                    $samples[] = [$r, $g, $b];
                }
            }
        }
        ksort($hist);

        return [
            'char' => chr($c),
            'code' => $c,
            'chroma' => $this->chroma,
            'hist' => $hist,
            'after_chroma_opaque' => $opaque,
            'green_opaque' => $green,
            'sample_rgb' => $samples,
        ];
    }

    public function measure(string $text, int $spacing = 5): int
    {
        // mainmenu UiList spacing=5
        $w = 0;
        $n = strlen($text);
        for ($i = 0; $i < $n; $i++) {
            $c = ord($text[$i]);
            $gw = $this->widths[$c] ?? 0;
            if ($gw <= 0) {
                continue;
            }
            $w += $gw + $spacing;
        }

        return max(0, $w - $spacing);
    }

    /**
     * Indexed compose: indices → skip chroma → palette RGB → RGBA.
     * @return array{w:int,h:int,rgba:string}
     */
    public function render(string $text, int $spacing = 5): array
    {
        $tw = $this->measure($text, $spacing);
        $th = $this->glyphH;
        if ($tw <= 0) {
            return ['w' => 1, 'h' => $th, 'rgba' => str_repeat("\0\0\0\0", $th)];
        }
        $out = str_repeat("\0\0\0\0", $tw * $th);
        $dstX = 0;
        $n = strlen($text);
        for ($i = 0; $i < $n; $i++) {
            $c = ord($text[$i]);
            $gw = min($this->widths[$c] ?? 0, $this->sheetW);
            if ($gw <= 0) {
                continue;
            }
            $sy = $c * $this->glyphH;
            for ($y = 0; $y < $this->glyphH; $y++) {
                $srcY = $sy + $y;
                if ($srcY >= $this->sheetH) {
                    break;
                }
                for ($x = 0; $x < $gw; $x++) {
                    $idx = ord($this->indices[$srcY * $this->sheetW + $x] ?? "\0");
                    if ($idx === $this->chroma) {
                        continue;
                    }
                    [$r, $g, $b] = $this->palette->rgb($idx);
                    $dx = $dstX + $x;
                    if ($dx < 0 || $dx >= $tw) {
                        continue;
                    }
                    $di = ($y * $tw + $dx) * 4;
                    $out[$di] = chr($r);
                    $out[$di + 1] = chr($g);
                    $out[$di + 2] = chr($b);
                    $out[$di + 3] = "\xFF";
                }
            }
            $dstX += $gw + $spacing;
        }

        return ['w' => $tw, 'h' => $th, 'rgba' => $out];
    }
}
