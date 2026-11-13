<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

/**
 * Microtile draw masks applied after CEL decode.
 *
 * Left/Right prefixes: drawing is bottom-to-top with
 * InitialPrefix (-32 for Left, +64 for Right) and PrefixIncrement (+2 / -2).
 *
 * Transparent: blends the entire tile via BlitPixelsBlended /
 * paletteTransparencyLookup (50% mix with destination). PHP approximates with
 * α=128 so SDL_BLEND / SoftwareFramebuffer average matches; NOT checkerboard.
 */
final class MaskType
{
    public const SOLID = 0;
    public const TRANSPARENT = 1;
    public const LEFT = 2;
    public const RIGHT = 3;

    /** DX BlitPixelsBlended stand-in alpha for SDL_BLEND. */
    public const BLEND_ALPHA = 128;

    /**
     * Post-process RGBA to match MaskType:
     * - Transparent: entire opaque texels → α=BLEND_ALPHA (BlitPixelsBlended)
     * - Left/Right: "transparent" prefix region → α=BLEND_ALPHA (RenderLineTransparent),
     *   not skip — blends that region with destination (🮐 = blended)
     */
    public static function apply(string $rgba, int $w, int $h, int $mask): string
    {
        if ($mask === self::SOLID || $rgba === '') {
            return $rgba;
        }
        $out = $rgba;
        if ($mask === self::TRANSPARENT) {
            for ($i = 3, $n = strlen($out); $i < $n; $i += 4) {
                if (ord($out[$i]) !== 0) {
                    $out[$i] = chr(self::BLEND_ALPHA);
                }
            }

            return $out;
        }
        for ($y = 0; $y < $h; $y++) {
            // processes lines from the bottom; y=0 in our buffer is the top.
            $fromBottom = $h - 1 - $y;
            for ($x = 0; $x < $w; $x++) {
                $blend = match ($mask) {
                    self::LEFT => self::leftTransparent($x, $fromBottom, $w),
                    self::RIGHT => self::rightTransparent($x, $fromBottom, $w),
                    default => false,
                };
                if ($blend) {
                    $i = ($y * $w + $x) * 4 + 3;
                    if (ord($out[$i]) !== 0) {
                        $out[$i] = chr(self::BLEND_ALPHA);
                    }
                }
            }
        }

        return $out;
    }

    /** Count opaque→mask-blend (or former skip) pixels for overdraw audit. */
    public static function countMaskedAway(string $before, string $after): int
    {
        $n = min(strlen($before), strlen($after));
        $c = 0;
        for ($i = 3; $i < $n; $i += 4) {
            $ba = ord($before[$i]);
            $aa = ord($after[$i]);
            if ($ba > 0 && ($aa === 0 || $aa === self::BLEND_ALPHA) && $aa !== $ba) {
                $c++;
            }
        }

        return $c;
    }

    /** Transparent (Left) prefix: InitialPrefix=-32, increment +2 per line from bottom. */
    private static function leftTransparent(int $x, int $fromBottom, int $w): bool
    {
        $prefix = -32 + 2 * $fromBottom;
        if ($prefix <= 0) {
            return false;
        }

        return $x < min($w, $prefix);
    }

    /**
     * Right mask: InitialPrefix=64, increment -2 — opaque prefix shrinks from the left
     * (transparent on the right). OpaqueFirst=true in trapezoid path.
     */
    private static function rightTransparent(int $x, int $fromBottom, int $w): bool
    {
        $prefix = 64 - 2 * $fromBottom;
        if ($prefix >= $w) {
            return false; // fully opaque
        }
        if ($prefix <= 0) {
            return true; // fully transparent
        }

        // OpaqueFirst: [0, prefix) opaque, [prefix, w) transparent
        return $x >= $prefix;
    }

    public static function name(int $mask): string
    {
        return match ($mask) {
            self::SOLID => 'Solid',
            self::TRANSPARENT => 'Transparent',
            self::LEFT => 'Left',
            self::RIGHT => 'Right',
            default => 'Unknown',
        };
    }
}
