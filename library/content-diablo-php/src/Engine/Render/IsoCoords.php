<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

/**
 * Authoritative iso coordinate spaces.
 *
 * Spaces:
 * - mega: dungeon[x][y] 40×40 mega tiles (DRLG)
 * - dPiece: dPiece[x][y] 112×112 piece tiles (ViewPosition lives here)
 * - micro: 32×32 (or triangle) CEL frames stacked on a piece
 * - screen: logical 640×(480−panel) framebuffer pixels
 * - ui: 640×480 DiabloUI rectangle
 */
final class IsoCoords
{
    public const TILE_WIDTH = 64;
    public const TILE_HEIGHT = 32;
    public const MICRO_WIDTH = 32;
 public const PANEL_HEIGHT = 128; // pin main panel (CalculatePanelAreas)
    public const SCREEN_W = 640;
    public const SCREEN_H = 480;

 /** world delta → screen (Displacement::worldToScreen) */
    public static function worldToScreen(int $dx, int $dy): array
    {
        return [
            ($dy - $dx) * 32,
            ($dy + $dx) * -16,
        ];
    }

    /** screen delta → world (screenToWorld) */
    public static function screenToWorld(int $sx, int $sy): array
    {
        // worldX = (2*sy + sx) / -64 ; worldY = (2*sy - sx) / -64
        return [
            (int) round((2 * $sy + $sx) / -64.0),
            (int) round((2 * $sy - $sx) / -64.0),
        ];
    }

 /** CalculateSpriteTileCenterX — offset from tile tip to sprite left. */
    public static function spriteTileCenterX(int $width): int
    {
        return intdiv($width - self::TILE_WIDTH, 2);
    }

    public static function viewportHeight(): int
    {
        // gnViewportHeight @ 640×480 with panel 128 → 352
        return self::SCREEN_H - self::PANEL_HEIGHT;
    }
}
