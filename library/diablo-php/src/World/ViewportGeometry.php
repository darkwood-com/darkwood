<?php

declare(strict_types=1);

namespace Diablo\World;

use Diablo\Engine\Render\IsoCoords;

/**
 * Viewport geometry (no zoom, no side panels).
 */
final class ViewportGeometry
{
    /**
     * @return array{
     *   tilesToTop:int,tilesToLeft:int,tileRows:int,tileColumns:int,
     *   tileOffsetX:int,tileOffsetY:int,tileShiftX:int,tileShiftY:int,
     *   firstX:int,firstY:int
     * }
     */
    public static function compute(int $viewX, int $viewY, int $screenW = IsoCoords::SCREEN_W, int $viewportH = 0): array
    {
        if ($viewportH <= 0) {
            $viewportH = IsoCoords::viewportHeight();
        }
        $tileW = IsoCoords::TILE_WIDTH;
        $tileH = IsoCoords::TILE_HEIGHT;
        $playerX = intdiv($screenW, 2);
        $playerY = intdiv($viewportH, 2);
        $tilesToTop = intdiv($playerY + $tileH - 1, $tileH);
        $tilesToLeft = intdiv($playerX + $tileW - 1, $tileW);
        $startX = $playerX - $tilesToLeft * $tileW;
        $startY = $playerY - $tilesToTop * $tileH;
        // North * top + West * left
        $shiftX = (-1) * $tilesToTop + (-1) * $tilesToLeft;
        $shiftY = (-1) * $tilesToTop + (1) * $tilesToLeft;
        if ($tilesToLeft * $tileW >= $playerX) {
            $startX += intdiv($tileW, 2);
            $startY += -intdiv($tileH, 2);
            // NorthEast
            $shiftY += -1;
        } elseif ($tilesToTop * $tileH < $playerY) {
            $startY += -$tileH;
            $shiftX += -1;
            $shiftY += -1;
        }
        $tileOffsetX = $startX - intdiv($tileW, 2);
        $tileOffsetY = $startY + intdiv($tileH, 2) - 1;
        $renderStartX = $startX - intdiv($tileW, 2);
        $renderStartY = $startY - intdiv($tileH, 2);
        $tileRows = intdiv($viewportH - $renderStartY + intdiv($tileH, 2) - 1, intdiv($tileH, 2));
        $tileColumns = intdiv($screenW - $renderStartX + $tileW - 1, $tileW);

        return [
            'tilesToTop' => $tilesToTop,
            'tilesToLeft' => $tilesToLeft,
            'tileRows' => $tileRows,
            'tileColumns' => $tileColumns,
            'tileOffsetX' => $tileOffsetX,
            'tileOffsetY' => $tileOffsetY,
            'tileShiftX' => $shiftX,
            'tileShiftY' => $shiftY,
            'firstX' => $viewX + $shiftX,
            'firstY' => $viewY + $shiftY,
        ];
    }
}
