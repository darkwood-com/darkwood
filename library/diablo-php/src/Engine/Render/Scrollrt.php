<?php

declare(strict_types=1);

namespace Diablo\Engine\Render;

use Diablo\Levels\DungeonMap;
use Diablo\Platform\Sdl;

/**
 * DrawGame pipeline:
 * DrawFloor → DrawTileContent(DrawDungeon) → DrawOOB.
 * TILE 64×32, East +{1,-1}/+64px, zigzag rows, micro L/R, stack y-=32.
 */
final class Scrollrt
{
    private static int $tileOffsetX = 0;
    private static int $tileOffsetY = 0;
    private static int $tileRows = 18;
    private static int $tileColumns = 14;
    private static int $tilesToTop = 8;
    private static int $tilesToLeft = 6;
    /** DX tileShift (world delta from ViewPosition to first drawn tile) */
    private static int $tileShiftX = 0;
    private static int $tileShiftY = 0;
    /** Last drawGame overscan-expanded bounds (diagnostics) */
    private static int $lastDrawRows = 18;
    private static int $lastDrawColumns = 14;
    /** @var array{0:int,1:int,2:int,3:int} */
    private static array $lastFirstTile = [0, 0, 0, 0];
    private static bool $disableMasks = false;
    public static bool $debugRenderPasses = false;
    public static bool $debugRenderState = false;
    /** @var array{0:int,1:int}|null */
    public static ?array $traceTile = null;
    /** Current cell being drawn (for player-draw trace) */
    public static int $currentTileX = 0;
    public static int $currentTileY = 0;
    /** @var array<string,int> */
    public static array $passCounts = [];

    public static function resetPassCounts(): void
    {
        self::$passCounts = [
            'DrawFloor_tiles' => 0,
            'DrawFloor_draws' => 0,
            'DrawCell_tiles' => 0,
            'DrawCell_draws' => 0,
            'DrawDungeon_ents' => 0,
            'DrawSpecial' => 0,
            'DrawOOB' => 0,
            'DrawPlayers' => 0,
            'DrawMonsters' => 0,
            'DrawObjects' => 0,
            'DrawItems' => 0,
            'DrawMissiles' => 0,
            'skipped_zero_piece_micros' => 0,
        ];
    }

    public static function emitPassReport(): void
    {
        if (!self::$debugRenderPasses) {
            return;
        }
        fwrite(STDOUT, "PASS_BEGIN DrawGame\n");
        foreach (self::$passCounts as $k => $v) {
            fwrite(STDOUT, "  {$k}={$v}\n");
        }
        fwrite(STDOUT, "PASS_END DrawGame\n");
    }

    private static function trace(string $msg): void
    {
        if (self::$traceTile === null) {
            return;
        }
        fwrite(STDOUT, 'trace-tile ' . self::$traceTile[0] . ',' . self::$traceTile[1] . ' ' . $msg . "\n");
    }

    private static function isTraceTile(int $tx, int $ty): bool
    {
        return self::$traceTile !== null && self::$traceTile[0] === $tx && self::$traceTile[1] === $ty;
    }

    private static function assertRenderState(Sdl $sdl, string $phase): void
    {
        if (!self::$debugRenderState) {
            return;
        }
        $c = $sdl->getDrawColor();
        fwrite(STDOUT, sprintf(
            "debug-render-state %s color=%d,%d,%d,%d colorMod=%d clip=%d\n",
            $phase,
            $c[0],
            $c[1],
            $c[2],
            $c[3],
            $sdl->usesColorMod ? 1 : 0,
            $sdl->usesClipRect ? 1 : 0
        ));
        if ($sdl->usesColorMod) {
            throw new \RuntimeException('debug-render-state: ColorMod must remain unused in PHP port');
        }
        if ($sdl->usesClipRect) {
            throw new \RuntimeException('debug-render-state: clip rect must remain unused in PHP port');
        }
    }

    /**
     * DrawView → DrawGame entry.
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     * @param callable(int,int):list<array{kind:string,payload:mixed}> $entitiesAt
     * @param callable(Sdl,array{kind:string,payload:mixed},int,int,int):void $drawEntity
     */
    public static function drawGame(
        Sdl $sdl,
        DungeonMap $map,
        int $viewX,
        int $viewY,
        int $screenW,
        int $viewportH,
        string $renderLayer,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders = false,
        int $scrollOffX = 0,
        int $scrollOffY = 0,
        bool $isWalking = false,
        int $walkDir = 0,
        int $renderMargin = 0,
        bool $disableMasks = false,
    ): void {
        self::resetPassCounts();
        self::calcViewportGeometry($screenW, $viewportH);
        [$tileX, $tileY, $bufX, $bufY] = self::calcFirstTilePosition($viewX, $viewY, $scrollOffX, $scrollOffY);

        $rows = self::$tileRows;
        $columns = self::$tileColumns;
        if ($isWalking) {
            [$dRows, $dCols] = self::walkOverscan($walkDir);
            $rows += $dRows;
            $columns += $dCols;
        }
        if ($renderMargin > 0) {
            $rows += $renderMargin * 2;
            $columns += $renderMargin * 2;
        }
        self::$lastDrawRows = $rows;
        self::$lastDrawColumns = $columns;
        self::$lastFirstTile = [$tileX, $tileY, $bufX, $bufY];
        self::$disableMasks = $disableMasks;

        $drawFloor = $renderLayer === 'all' || $renderLayer === 'floor' || $renderLayer === 'micros';
        $drawWalls = $renderLayer === 'all' || $renderLayer === 'walls' || $renderLayer === 'micros';
        $drawEnts = $renderLayer === 'all' || $renderLayer === 'entities' || $renderLayer === 'actors'
            || $renderLayer === 'objects' || $renderLayer === 'specials';
        if ($renderLayer === 'specials') {
            $drawFloor = false;
            $drawWalls = false;
            $drawEnts = true;
        }

        // DrawGame: DrawFloor → DrawTileContent → DrawOOB
        self::assertRenderState($sdl, 'DrawGame_BEGIN');
        if ($drawFloor) {
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_BEGIN DrawFloor\n");
            }
            self::assertRenderState($sdl, 'DrawFloor_BEGIN');
            \Diablo\Engine\Render\RenderTrace::setLayer('floor');
            self::drawFloor(
                $sdl,
                $map,
                $tileX,
                $tileY,
                $bufX,
                $bufY,
                $rows,
                $columns,
                $blitFloorMicro,
                $blitWallMicro,
                $entitiesAt,
                $drawEntity,
                $debugPlaceholders
            );
            self::assertRenderState($sdl, 'DrawFloor_END');
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_END DrawFloor tiles=" . self::$passCounts['DrawFloor_tiles']
                    . " draws=" . self::$passCounts['DrawFloor_draws'] . "\n");
            }
        }
        if ($drawWalls || $drawEnts) {
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_BEGIN DrawTileContent\n");
            }
            self::assertRenderState($sdl, 'DrawTileContent_BEGIN');
            \Diablo\Engine\Render\RenderTrace::setLayer('walls');
            self::drawTileContent(
                $sdl,
                $map,
                $tileX,
                $tileY,
                $bufX,
                $bufY,
                $rows + $map->microTileLen,
                $columns,
                $drawWalls,
                $drawEnts,
                $blitFloorMicro,
                $blitWallMicro,
                $entitiesAt,
                $drawEntity,
                $debugPlaceholders,
                $renderLayer
            );
            // Placeholders restore draw color; ensure no ColorMod/clip leak
            $sdl->setDrawColor(0, 0, 0, 255);
            self::assertRenderState($sdl, 'DrawTileContent_END');
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_END DrawTileContent cells=" . self::$passCounts['DrawCell_tiles']
                    . " ents=" . self::$passCounts['DrawDungeon_ents']
                    . " specials=" . self::$passCounts['DrawSpecial'] . "\n");
            }
        }
        if ($renderLayer === 'all' || $renderLayer === 'floor' || $renderLayer === 'walls') {
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_BEGIN DrawOOB\n");
            }
            \Diablo\Engine\Render\RenderTrace::setLayer('oob');
            self::drawOOB(
                $sdl,
                $map,
                $tileX,
                $tileY,
                $bufX,
                $bufY,
                $rows + 5,
                $columns,
                $blitFloorMicro,
                $blitWallMicro
            );
            if (self::$debugRenderPasses) {
                fwrite(STDOUT, "PASS_END DrawOOB count=" . self::$passCounts['DrawOOB'] . "\n");
            }
        }
        self::assertRenderState($sdl, 'DrawGame_END');
        self::emitPassReport();
    }

    /** @deprecated Use drawGame — kept as DrawView alias */
    public static function drawView(
        Sdl $sdl,
        DungeonMap $map,
        int $viewX,
        int $viewY,
        int $screenW,
        int $viewportH,
        string $renderLayer,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders = false,
        int $scrollOffX = 0,
        int $scrollOffY = 0,
        bool $isWalking = false,
        int $walkDir = 0,
        int $renderMargin = 0,
        bool $disableMasks = false,
    ): void {
        self::drawGame(
            $sdl,
            $map,
            $viewX,
            $viewY,
            $screenW,
            $viewportH,
            $renderLayer,
            $blitFloorMicro,
            $blitWallMicro,
            $entitiesAt,
            $drawEntity,
            $debugPlaceholders,
            $scrollOffX,
            $scrollOffY,
            $isWalking,
            $walkDir,
            $renderMargin,
            $disableMasks
        );
    }

    /**
     * DrawFloor.
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     * @param callable(int,int):list<array{kind:string,payload:mixed}> $entitiesAt
     * @param callable(Sdl,array{kind:string,payload:mixed},int,int,int):void $drawEntity
     */
    private static function drawFloor(
        Sdl $sdl,
        DungeonMap $map,
        int $tileX,
        int $tileY,
        int $bufX,
        int $bufY,
        int $rows,
        int $columns,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders,
    ): void {
        self::isoPass(
            $sdl,
            $map,
            $tileX,
            $tileY,
            $bufX,
            $bufY,
            $rows,
            $columns,
            true,
            false,
            false,
            $blitFloorMicro,
            $blitWallMicro,
            $entitiesAt,
            $drawEntity,
            $debugPlaceholders,
            'all'
        );
    }

    /**
     * DrawTileContent (rows already include MicroTileLen).
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     * @param callable(int,int):list<array{kind:string,payload:mixed}> $entitiesAt
     * @param callable(Sdl,array{kind:string,payload:mixed},int,int,int):void $drawEntity
     */
    private static function drawTileContent(
        Sdl $sdl,
        DungeonMap $map,
        int $tileX,
        int $tileY,
        int $bufX,
        int $bufY,
        int $rows,
        int $columns,
        bool $drawWalls,
        bool $drawEnts,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders,
        string $renderLayer,
    ): void {
        self::isoPass(
            $sdl,
            $map,
            $tileX,
            $tileY,
            $bufX,
            $bufY,
            $rows,
            $columns,
            false,
            $drawWalls,
            $drawEnts,
            $blitFloorMicro,
            $blitWallMicro,
            $entitiesAt,
            $drawEntity,
            $debugPlaceholders,
            $renderLayer
        );
    }

    /**
     * DrawOOB — town: black diamond; dungeon: DrawDirtTile (2×2 sample at 0,0).
     *
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     */
    private static function drawOOB(
        Sdl $sdl,
        DungeonMap $map,
        int $tileX,
        int $tileY,
        int $bufX,
        int $bufY,
        int $rows,
        int $columns,
        callable $blitFloorMicro,
        callable $blitWallMicro,
    ): void {
        $isTown = $map->levelType === DungeonMap::DTYPE_TOWN;
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $columns; $j++) {
                if (!$map->inBounds($tileX, $tileY)) {
                    if ($isTown) {
                        self::drawBlackTile($sdl, $bufX, $bufY);
                    } else {
                        self::drawDirtTile($sdl, $map, $tileX, $tileY, $bufX, $bufY, $blitFloorMicro, $blitWallMicro);
                    }
                    self::$passCounts['DrawOOB']++;
                }
                $tileX++;
                $tileY--;
                $bufX += IsoCoords::TILE_WIDTH;
            }
            $tileX -= $columns;
            $tileY += $columns;
            $bufX -= $columns * IsoCoords::TILE_WIDTH;
            $bufY += intdiv(IsoCoords::TILE_HEIGHT, 2);
            if (($i & 1) !== 0) {
                $tileX++;
                $columns--;
                $bufX += intdiv(IsoCoords::TILE_WIDTH, 2);
            } else {
                $tileY++;
                $columns++;
                $bufX -= intdiv(IsoCoords::TILE_WIDTH, 2);
            }
        }
    }

    /**
     * DrawDirtTile — sample 2×2 dirt pattern at dungeon (0,0) keyed by OOB parity.
     *
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     */
    private static function drawDirtTile(
        Sdl $sdl,
        DungeonMap $map,
        int $tileX,
        int $tileY,
        int $bufX,
        int $bufY,
        callable $blitFloorMicro,
        callable $blitWallMicro,
    ): void {
        $sx = $tileX & 1;
        $sy = $tileY & 1;
        $piece = (int) ($map->dPiece[$sy][$sx] ?? 0);
        // Piece 0 is a valid MIN entry (DX zero-index); only missing micros → black.
        if (!isset($map->dPieceMicros[$piece])) {
            self::drawBlackTile($sdl, $bufX, $bufY);

            return;
        }
        $light = (int) ($map->dLight[$sy][$sx] ?? 15);
        $isFloor = $map->isFloorTile($sx, $sy);
        // Floor triangles are drawn in DrawFloor for in-bounds tiles; OOB must compose them here.
        if ($isFloor) {
            $blitFloorMicro($sdl, $piece, 0, $bufX, $bufY, $light, 'floor_triangle', MaskType::SOLID);
            $blitFloorMicro($sdl, $piece, 1, $bufX + IsoCoords::MICRO_WIDTH, $bufY, $light, 'floor_triangle', MaskType::SOLID);
        }
        self::drawCell($sdl, $map, $sx, $sy, $bufX, $bufY, $piece, $light, $isFloor, true, $blitWallMicro, false);
    }

    /**
     * DrawGame walk overscan — rows/columns added while isWalking.
     * @return array{0:int,1:int} deltaRows, deltaColumns
     */
    public static function walkOverscan(int $dir): array
    {
        return match ($dir) {
            \Diablo\Engine\Direction::N, \Diablo\Engine\Direction::S => [2, 0],
            \Diablo\Engine\Direction::NE => [2, 1],
            \Diablo\Engine\Direction::E, \Diablo\Engine\Direction::W => [0, 1],
            \Diablo\Engine\Direction::SE, \Diablo\Engine\Direction::SW, \Diablo\Engine\Direction::NW => [1, 1],
            default => [0, 0],
        };
    }

    private static function calcViewportGeometry(int $screenW, int $viewportH): void
    {
        // CalcViewportGeometry (no zoom, no side panels)
        $tileW = IsoCoords::TILE_WIDTH;
        $tileH = IsoCoords::TILE_HEIGHT;
        $playerX = intdiv($screenW, 2);
        $playerY = intdiv($viewportH, 2);
        self::$tilesToTop = intdiv($playerY + $tileH - 1, $tileH);
        self::$tilesToLeft = intdiv($playerX + $tileW - 1, $tileW);
        $startX = $playerX - self::$tilesToLeft * $tileW;
        $startY = $playerY - self::$tilesToTop * $tileH;
        // North * top + West * left
        self::$tileShiftX = (-1) * self::$tilesToTop + (-1) * self::$tilesToLeft;
        self::$tileShiftY = (-1) * self::$tilesToTop + (1) * self::$tilesToLeft;
        if (self::$tilesToLeft * $tileW >= $playerX) {
            $startX += intdiv($tileW, 2);
            $startY += -intdiv($tileH, 2);
            // NorthEast
            self::$tileShiftY += -1;
        } elseif (self::$tilesToTop * $tileH < $playerY) {
            $startY += -$tileH;
            self::$tileShiftX += -1;
            self::$tileShiftY += -1;
        }
        self::$tileOffsetX = $startX - intdiv($tileW, 2);
        self::$tileOffsetY = $startY + intdiv($tileH, 2) - 1;
        $renderStartX = $startX - intdiv($tileW, 2);
        $renderStartY = $startY - intdiv($tileH, 2);
        self::$tileRows = intdiv($viewportH - $renderStartY + intdiv($tileH, 2) - 1, intdiv($tileH, 2));
        self::$tileColumns = intdiv($screenW - $renderStartX + $tileW - 1, $tileW);
    }

    /** @return array{tileRows:int,tileColumns:int,tilesToTop:int,tilesToLeft:int,tileOffsetX:int,tileOffsetY:int,tileShiftX:int,tileShiftY:int,drawRows:int,drawColumns:int,firstTile:array{0:int,1:int,2:int,3:int}} */
    public static function viewportStats(): array
    {
        return [
            'tileRows' => self::$tileRows,
            'tileColumns' => self::$tileColumns,
            'tilesToTop' => self::$tilesToTop,
            'tilesToLeft' => self::$tilesToLeft,
            'tileOffsetX' => self::$tileOffsetX,
            'tileOffsetY' => self::$tileOffsetY,
            'tileShiftX' => self::$tileShiftX,
            'tileShiftY' => self::$tileShiftY,
            'drawRows' => self::$lastDrawRows,
            'drawColumns' => self::$lastDrawColumns,
            'firstTile' => self::$lastFirstTile,
        ];
    }

    /** Force geometry calc for diagnostics without drawing. */
    public static function prepareViewport(int $screenW, int $viewportH): void
    {
        self::calcViewportGeometry($screenW, $viewportH);
    }

    /** @return array{0:int,1:int,2:int,3:int} */
    public static function firstTile(int $viewX, int $viewY, int $scrollOffX = 0, int $scrollOffY = 0): array
    {
        return self::calcFirstTilePosition($viewX, $viewY, $scrollOffX, $scrollOffY);
    }

    /** @return array{0:int,1:int,2:int,3:int} tileX,tileY,bufX,bufY */
    private static function calcFirstTilePosition(int $viewX, int $viewY, int $scrollOffX = 0, int $scrollOffY = 0): array
    {
        // CalcFirstTilePosition: ViewPosition + tileShift; buffer at tileOffset + walk scroll
        $sx = $viewX + self::$tileShiftX;
        $sy = $viewY + self::$tileShiftY;

        return [$sx, $sy, self::$tileOffsetX + $scrollOffX, self::$tileOffsetY + $scrollOffY];
    }

    /**
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     * @param callable(int,int):list<array{kind:string,payload:mixed}> $entitiesAt
     * @param callable(Sdl,array{kind:string,payload:mixed},int,int,int):void $drawEntity
     */
    private static function isoPass(
        Sdl $sdl,
        DungeonMap $map,
        int $tileX,
        int $tileY,
        int $bufX,
        int $bufY,
        int $rows,
        int $columns,
        bool $floorOnly,
        bool $drawWalls,
        bool $drawEnts,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders,
        string $renderLayer = 'all',
    ): void {
        for ($i = 0; $i < $rows; $i++) {
            $skip = false;
            for ($j = 0; $j < $columns; $j++) {
                $skipNext = false;
                if ($map->inBounds($tileX, $tileY)) {
                    // : peek tilePosition + Direction::East = (+1,-1), then skipNext that East cell
                    if (!$floorOnly && $tileX + 1 < DungeonMap::MAXDUNX && $tileY - 1 >= 0 && $bufX + IsoCoords::TILE_WIDTH <= IsoCoords::SCREEN_W) {
                        if (self::isWall($map, $tileX, $tileY) && (self::isWall($map, $tileX + 1, $tileY) || ($tileX > 0 && self::isWall($map, $tileX - 1, $tileY)))) {
                            if (self::isTileNotSolid($map, $tileX + 1, $tileY - 1) && self::isTileNotSolid($map, $tileX, $tileY - 1)) {
                                self::drawDungeon(
                                    $sdl,
                                    $map,
                                    $tileX + 1,
                                    $tileY - 1,
                                    $bufX + IsoCoords::TILE_WIDTH,
                                    $bufY,
                                    false,
                                    $drawWalls,
                                    $drawEnts,
                                    $blitFloorMicro,
                                    $blitWallMicro,
                                    $entitiesAt,
                                    $drawEntity,
                                    $debugPlaceholders,
                                    $renderLayer
                                );
                                $skipNext = true;
                            }
                        }
                    }
                    if (!$skip) {
                        self::drawDungeon(
                            $sdl,
                            $map,
                            $tileX,
                            $tileY,
                            $bufX,
                            $bufY,
                            $floorOnly,
                            $drawWalls,
                            $drawEnts,
                            $blitFloorMicro,
                            $blitWallMicro,
                            $entitiesAt,
                            $drawEntity,
                            $debugPlaceholders,
                            $renderLayer
                        );
                    }
                    $skip = $skipNext;
                }
                // East
                $tileX++;
                $tileY--;
                $bufX += IsoCoords::TILE_WIDTH;
            }
            // rewind West * columns
            $tileX -= $columns;
            $tileY += $columns;
            $bufX -= $columns * IsoCoords::TILE_WIDTH;
            // next row
            $bufY += intdiv(IsoCoords::TILE_HEIGHT, 2);
            if (($i & 1) !== 0) {
                $tileX++;
                $columns--;
                $bufX += intdiv(IsoCoords::TILE_WIDTH, 2);
            } else {
                $tileY++;
                $columns++;
                $bufX -= intdiv(IsoCoords::TILE_WIDTH, 2);
            }
        }
    }

    /**
     * DrawDungeon — DrawCell then entities then specials.
     *
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitFloorMicro
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     * @param callable(int,int):list<array{kind:string,payload:mixed}> $entitiesAt
     * @param callable(Sdl,array{kind:string,payload:mixed},int,int,int):void $drawEntity
     */
    private static function drawDungeon(
        Sdl $sdl,
        DungeonMap $map,
        int $tx,
        int $ty,
        int $bx,
        int $by,
        bool $floorOnly,
        bool $drawWalls,
        bool $drawEnts,
        callable $blitFloorMicro,
        callable $blitWallMicro,
        callable $entitiesAt,
        callable $drawEntity,
        bool $debugPlaceholders,
        string $renderLayer = 'all',
    ): void {
        $piece = $map->dPiece[$ty][$tx] ?? 0;
        $light = $map->dLight[$ty][$tx] ?? 0;
        $isFloor = $map->isFloorTile($tx, $ty);
        $traced = self::isTraceTile($tx, $ty);
        self::$currentTileX = $tx;
        self::$currentTileY = $ty;

        // DrawFloor pass — DrawFloorTile only
        if ($floorOnly) {
            self::$passCounts['DrawFloor_tiles']++;
            if ($isFloor) {
                $drew = $blitFloorMicro($sdl, $piece, 0, $bx, $by, $light, 'floor_triangle', MaskType::SOLID);
                $drew = $blitFloorMicro($sdl, $piece, 1, $bx + IsoCoords::MICRO_WIDTH, $by, $light, 'floor_triangle', MaskType::SOLID) || $drew;
                if ($drew) {
                    self::$passCounts['DrawFloor_draws']++;
                }
                if ($traced) {
                    self::trace($drew ? 'Floor drawn' : 'Floor blit failed piece=' . $piece);
                }
                if (!$drew && $debugPlaceholders) {
                    $sdl->setDrawColor(40, 40, 50);
                    self::drawBlackTile($sdl, $bx, $by);
                }
            } elseif ($traced) {
                self::trace('Floor skipped not-floor piece=' . $piece);
            }

            return;
        }

        // DrawDungeon: DrawCell + entities
        \Diablo\Engine\Render\RenderTrace::setLayer('walls');
        self::drawCell($sdl, $map, $tx, $ty, $bx, $by, $piece, $light, $isFloor, $drawWalls, $blitWallMicro, $traced);

        if (!$drawEnts) {
            return;
        }
        $ents = $entitiesAt($tx, $ty);
        if ($renderLayer === 'specials') {
            $ents = [];
        } elseif ($renderLayer === 'actors') {
            $ents = array_values(array_filter($ents, static fn ($e) => in_array($e['kind'], ['player', 'monster', 'dead_player'], true)));
        } elseif ($renderLayer === 'objects') {
            $ents = array_values(array_filter($ents, static fn ($e) => in_array($e['kind'], ['object', 'object_pre', 'item', 'item_pre', 'corpse', 'missile', 'missile_pre'], true)));
        }
        // DrawDungeon order: missile_pre → corpse → object_pre → item_pre → dead_player → player → monster → missile → object → item
        $order = ['missile_pre', 'corpse', 'object_pre', 'item_pre', 'dead_player', 'player', 'monster', 'missile', 'object', 'item'];
        $byKind = [];
        foreach ($ents as $e) {
            $byKind[$e['kind']][] = $e;
        }
        $ex = $bx + 32;
        $ey = $by;
        if ($renderLayer !== 'specials') {
            foreach ($order as $kind) {
                foreach ($byKind[$kind] ?? [] as $e) {
                    \Diablo\Engine\Render\RenderTrace::setLayer('entity_' . $kind);
                    $drawEntity($sdl, $e, $ex, $ey, $light);
                    self::$passCounts['DrawDungeon_ents']++;
                    match ($kind) {
                        'player', 'dead_player' => self::$passCounts['DrawPlayers']++,
                        'monster' => self::$passCounts['DrawMonsters']++,
                        'object', 'object_pre' => self::$passCounts['DrawObjects']++,
                        'item', 'item_pre' => self::$passCounts['DrawItems']++,
                        'missile', 'missile_pre' => self::$passCounts['DrawMissiles']++,
                        default => null,
                    };
                    if ($traced) {
                        self::trace('Entity ' . $kind);
                    }
                }
            }
        }
        if ($renderLayer === 'all' || $renderLayer === 'objects' || $renderLayer === 'entities' || $renderLayer === 'specials') {
            if ($map->levelType === DungeonMap::DTYPE_TOWN) {
                if ($tx > 0 && $ty > 0 && $by > IsoCoords::TILE_HEIGHT) {
                    $spec = $map->dSpecial[$ty - 1][$tx - 1] ?? 0;
                    if ($spec > 0) {
                        \Diablo\Engine\Render\RenderTrace::setLayer('special_town');
                        $drawEntity(
                            $sdl,
                            ['kind' => 'special', 'payload' => ['id' => $spec, 'trans' => $map->dTransVal[$ty - 1][$tx - 1] ?? 0, 'branch' => 'town_deferred']],
                            $ex,
                            $ey - IsoCoords::TILE_HEIGHT,
                            $light
                        );
                        self::$passCounts['DrawSpecial']++;
                        if ($traced) {
                            self::trace('Special town delayed id=' . $spec);
                        }
                    }
                }
            } else {
                $spec = $map->dSpecial[$ty][$tx] ?? 0;
                if ($spec > 0) {
                    $trans = (int) ($map->dTransVal[$ty][$tx] ?? 0);
                    $blend = $map->transListActive($trans);
                    \Diablo\Engine\Render\RenderTrace::setLayer('special_dungeon');
                    $drawEntity(
                        $sdl,
                        ['kind' => 'special', 'payload' => ['id' => $spec, 'trans' => $trans, 'blend' => $blend, 'branch' => $blend ? 'dungeon_lit_blend' : 'dungeon_lit']],
                        $ex,
                        $ey,
                        $light
                    );
                    self::$passCounts['DrawSpecial']++;
                    if ($traced) {
                        self::trace('Special dungeon id=' . $spec . ($blend ? ' blend' : ' lit'));
                    }
                }
            }
        }
    }

    /**
     * DrawCell — wall/foliage/upper micros (skipped when micros table missing).
     *
     * @param callable(Sdl,int,int,int,int,int,string):bool $blitWallMicro
     */
    private static function drawCell(
        Sdl $sdl,
        DungeonMap $map,
        int $tx,
        int $ty,
        int $bx,
        int $by,
        int $piece,
        int $light,
        bool $isFloor,
        bool $drawWalls,
        callable $blitWallMicro,
        bool $traced,
    ): void {
        self::$passCounts['DrawCell_tiles']++;
        if (!$drawWalls || !isset($map->dPieceMicros[$piece])) {
            if ($traced && !isset($map->dPieceMicros[$piece])) {
                self::trace('DrawCell micros missing piece=' . $piece);
            }

            return;
        }
        $mt = $map->dPieceMicros[$piece];
        $n = count($mt);
        $sol = $map->pieceSol($piece);
        $transVal = (int) ($map->dTransVal[$ty][$tx] ?? 0);
        $t0 = (int) ($mt[0]['type'] ?? 0);
        $t1 = (int) ($mt[1]['type'] ?? 0);
        if (self::$disableMasks) {
            $maskLeft = MaskType::SOLID;
            $maskRight = MaskType::SOLID;
            $upperMask = MaskType::SOLID;
        } else {
            $transparency = ($sol & DungeonMap::SOL_TRANSPARENT) !== 0 && $map->transListActive($transVal);
            $maskLeft = self::firstTileMaskLeft($t0, $sol, $transparency);
            $maskRight = self::firstTileMaskRight($t1, $sol, $transparency);
            $upperMask = $transparency ? MaskType::TRANSPARENT : MaskType::SOLID;
        }
        if ($isFloor) {
            if ($t0 === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                // Foliage is always Solid (never TransList-blended).
                $blitWallMicro($sdl, $piece, 0, $bx, $by - 16, $light, 'foliage', MaskType::SOLID);
                self::$passCounts['DrawCell_draws']++;
                if ($traced) {
                    self::trace('Foliage L');
                }
            }
            if ($t1 === DunMicro::TYPE_TRANSPARENT_SQUARE) {
                $blitWallMicro($sdl, $piece, 1, $bx + IsoCoords::MICRO_WIDTH, $by - 16, $light, 'foliage', MaskType::SOLID);
                self::$passCounts['DrawCell_draws']++;
                if ($traced) {
                    self::trace('Foliage R');
                }
            }
        } else {
            $blitWallMicro($sdl, $piece, 0, $bx, $by, $light, '', $maskLeft);
            $blitWallMicro($sdl, $piece, 1, $bx + IsoCoords::MICRO_WIDTH, $by, $light, '', $maskRight);
            self::$passCounts['DrawCell_draws'] += 2;
            if ($traced) {
                self::trace('Wall base maskL=' . $maskLeft . ' maskR=' . $maskRight);
            }
        }
        $posY = $by - IsoCoords::TILE_HEIGHT;
        for ($b = 2; $b < $n; $b += 2) {
            $blitWallMicro($sdl, $piece, $b, $bx, $posY, $light, '', $upperMask);
            if ($b + 1 < $n) {
                $blitWallMicro($sdl, $piece, $b + 1, $bx + IsoCoords::MICRO_WIDTH, $posY, $light, '', $upperMask);
            }
            $posY -= IsoCoords::TILE_HEIGHT;
        }
    }

    /** getFirstTileMaskLeft */
    private static function firstTileMaskLeft(int $tileType, int $sol, bool $transparency): int
    {
        if (!$transparency) {
            return MaskType::SOLID;
        }
        if ($tileType === DunMicro::TYPE_LEFT_TRIANGLE) {
            return MaskType::SOLID;
        }
        if ($tileType === DunMicro::TYPE_TRANSPARENT_SQUARE || $tileType === DunMicro::TYPE_LEFT_TRAPEZOID) {
            return (($sol & DungeonMap::SOL_TRANSPARENT_LEFT) !== 0) ? MaskType::LEFT : MaskType::SOLID;
        }

        return MaskType::TRANSPARENT;
    }

    /** getFirstTileMaskRight */
    private static function firstTileMaskRight(int $tileType, int $sol, bool $transparency): int
    {
        if (!$transparency) {
            return MaskType::SOLID;
        }
        if ($tileType === DunMicro::TYPE_RIGHT_TRIANGLE) {
            return MaskType::SOLID;
        }
        if ($tileType === DunMicro::TYPE_TRANSPARENT_SQUARE || $tileType === DunMicro::TYPE_RIGHT_TRAPEZOID) {
            return (($sol & DungeonMap::SOL_TRANSPARENT_RIGHT) !== 0) ? MaskType::RIGHT : MaskType::SOLID;
        }

        return MaskType::TRANSPARENT;
    }

    public static function isWall(DungeonMap $map, int $x, int $y): bool
    {
        return $map->inBounds($x, $y) && $map->isSolid($x, $y);
    }

    public static function isTileNotSolid(DungeonMap $map, int $x, int $y): bool
    {
        return $map->inBounds($x, $y) && !$map->isSolid($x, $y);
    }

    /** @return array{0:int,1:int} */
    public static function screenToTile(int $sx, int $sy, int $viewX, int $viewY, int $screenW, int $viewportH, int $scrollOffX = 0, int $scrollOffY = 0): array
    {
        return self::screenToTileEx($sx, $sy, $viewX, $viewY, $screenW, $viewportH, $scrollOffX, $scrollOffY)['tile'];
    }

    /**
     * CalcTileOffset (cursor AlterMousePositionViaPlayer).
     * @return array{0:int,1:int}
     */
    public static function calcTileOffset(int $screenW, int $viewportH): array
    {
        $x = $screenW % IsoCoords::TILE_WIDTH;
        $y = $viewportH % IsoCoords::TILE_HEIGHT;
        if ($x !== 0) {
            $x = intdiv(IsoCoords::TILE_WIDTH - $x, 2);
        }
        if ($y !== 0) {
            $y = intdiv(IsoCoords::TILE_HEIGHT - $y, 2);
        }

        return [$x, $y];
    }

    /**
     * TilesInView (non-zoom).
     * @return array{0:int,1:int} columns, rows
     */
    public static function tilesInView(int $screenW, int $viewportH): array
    {
        $columns = intdiv($screenW, IsoCoords::TILE_WIDTH);
        if (($screenW % IsoCoords::TILE_WIDTH) !== 0) {
            $columns++;
        }
        $rows = intdiv($viewportH, IsoCoords::TILE_HEIGHT);
        if (($viewportH % IsoCoords::TILE_HEIGHT) !== 0) {
            $rows++;
        }

        return [$columns, $rows];
    }

 /** ShiftGrid — iso screen-space shift of a dPiece point. */
    public static function shiftGrid(int &$x, int &$y, int $horizontal, int $vertical): void
    {
        $x += $vertical + $horizontal;
        $y += $vertical - $horizontal;
    }

    /**
     * ConvertToTileGrid + ShiftToDiamondGridAlignment.
     * $scrollOffX/Y: camera-mode walk offset subtracted from screen (AlterMousePositionViaPlayer).
     * @return array{tile:array{0:int,1:int},flipflag:bool,localX:int,localY:int}
     */
    public static function screenToTileEx(
        int $sx,
        int $sy,
        int $viewX,
        int $viewY,
        int $screenW,
        int $viewportH,
        int $scrollOffX = 0,
        int $scrollOffY = 0,
    ): array {
        // AlterMousePositionViaPlayer: +CalcTileOffset, −camera walk offset
        [$xo, $yo] = self::calcTileOffset($screenW, $viewportH);
        $sx = $sx + $xo - $scrollOffX;
        $sy = $sy + $yo - $scrollOffY;

        [$columns, $rows] = self::tilesInView($screenW, $viewportH);
        // RowsCoveredByPanel: panel spans full 640 → 0 at classic res
        $lrow = $rows;

        $tileX = $viewX;
        $tileY = $viewY;
        self::shiftGrid($tileX, $tileY, -intdiv($columns, 2), -intdiv($lrow, 2));

        if (($columns % 2) === 0 && ($lrow % 2) === 0) {
            $sy += intdiv(IsoCoords::TILE_HEIGHT, 2);
        } elseif (($columns % 2) !== 0 && ($lrow % 2) !== 0) {
            $sx -= intdiv(IsoCoords::TILE_WIDTH, 2);
        } elseif (($columns % 2) !== 0 && ($lrow % 2) === 0) {
            $tileY++;
        }

        $tx = intdiv($sx, IsoCoords::TILE_WIDTH);
        $ty = intdiv($sy, IsoCoords::TILE_HEIGHT);
        self::shiftGrid($tileX, $tileY, $tx, $ty);

        $px = (($sx % IsoCoords::TILE_WIDTH) + IsoCoords::TILE_WIDTH) % IsoCoords::TILE_WIDTH;
        $py = (($sy % IsoCoords::TILE_HEIGHT) + IsoCoords::TILE_HEIGHT) % IsoCoords::TILE_HEIGHT;
        $flipy = $py < intdiv($px, 2);
        $flipx = $py >= IsoCoords::TILE_HEIGHT - intdiv($px, 2);
        if ($flipy) {
            $tileY--;
        }
        if ($flipx) {
            $tileX++;
        }
        $flipflag = ($flipy && $flipx) || (($flipy || $flipx) && $px < intdiv(IsoCoords::TILE_WIDTH, 2));

        return [
            'tile' => [$tileX, $tileY],
            'flipflag' => $flipflag,
            'localX' => $px,
            'localY' => $py,
        ];
    }

    /**
     * world_draw_black_tile — left+right opaque black triangles (h=31).
     * Dual-writes SDL + software framebuffer when --software-compose is active.
     */
    public static function drawBlackTile(Sdl $sdl, int $sx, int $syBottom): void
    {
        $sdl->setDrawColor(0, 0, 0, 255);
        self::fillBlackTrianglesSdl($sdl, $sx, $syBottom);
        if (RenderTrace::$softwareCompose && RenderTrace::$fb !== null) {
            self::fillBlackTrianglesSoft($sx, $syBottom);
        }
    }

    /** Opaque L/R isometric triangles via SDL (DunFrameTriangleHeight = 31). */
    private static function fillBlackTrianglesSdl(Sdl $sdl, int $sx, int $syBottom): void
    {
        $h = DunMicro::TRIANGLE_HEIGHT; // 31
        $top = $syBottom - ($h - 1);
        for ($row = 0; $row < $h; $row++) {
            $fromBottom = $h - 1 - $row;
            if ($fromBottom < 16) {
                $width = ($fromBottom + 1) * 2;
            } else {
                $width = (31 - $fromBottom) * 2;
            }
            $sdl->fillRect($sx + (32 - $width), $top + $row, $width, 1);
            $sdl->fillRect($sx + 32, $top + $row, $width, 1);
        }
    }

    /** Opaque L/R isometric triangles into soft FB (DunFrameTriangleHeight = 31). */
    private static function fillBlackTrianglesSoft(int $sx, int $syBottom): void
    {
        $fb = RenderTrace::$fb;
        if ($fb === null) {
            return;
        }
        $h = DunMicro::TRIANGLE_HEIGHT; // 31
        $top = $syBottom - ($h - 1);
        for ($row = 0; $row < $h; $row++) {
            $fromBottom = $h - 1 - $row;
            if ($fromBottom < 16) {
                $width = ($fromBottom + 1) * 2;
            } else {
                $width = (31 - $fromBottom) * 2;
            }
            // Left triangle: right-aligned in [sx, sx+32)
            $fb->fillRect($sx + (32 - $width), $top + $row, $width, 1, 0, 0, 0, 255);
            // Right triangle: left-aligned in [sx+32, sx+64)
            $fb->fillRect($sx + 32, $top + $row, $width, 1, 0, 0, 0, 255);
        }
    }

    public static function fillDiamond(Sdl $sdl, int $cx, int $cy, int $hw, int $hh): void
    {
        for ($dy = -$hh; $dy <= $hh; $dy++) {
            $t = $hh === 0 ? 0.0 : (1.0 - (abs($dy) / $hh));
            $w = (int) round($hw * $t);
            $sdl->fillRect($cx - $w, $cy + $dy, max(1, $w * 2), 1);
        }
    }
}
