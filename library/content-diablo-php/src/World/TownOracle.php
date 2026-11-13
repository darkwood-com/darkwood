<?php

declare(strict_types=1);

namespace Diablo\World;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Levels\DungeonMap;

/**
 * Deterministic town dPiece builder for oracle dumps.
 * PlaceDunTiles updates dungeon[] only () — never dPiece.
 * Hellfire hive/grave closed patches omitted (vanilla diabdat).
 */
final class TownOracle
{
    /**
     * Build a DungeonMap whose dPiece matches vanilla DrlgTPass3 (closed warps).
     */
    public static function build(AssetStore $assets): DungeonMap
    {
        $map = new DungeonMap();
        $map->levelType = DungeonMap::DTYPE_TOWN;
        $map->currLevel = 0;
        $map->microTileLen = 16;
        $map->clear();
        $map->palette = $assets->loadPalette('levels\\towndata\\town.pal');
        $map->loadTownGraphics($assets);
        self::drlgTPass3($map, $assets);
        $map->viewX = 75;
        $map->viewY = 68;
        WorldCellAudit::stageDrlg($map, 'TownOracle::DrlgTPass3');
        WorldCellAudit::stageMicros($map);

        return $map;
    }

    /** DrlgTPass3 (vanilla closed warps, Q_PWATER inactive → tile 71). */
    private static function drlgTPass3(DungeonMap $map, AssetStore $assets): void
    {
        // Prefill 426
        for ($yy = 0; $yy < DungeonMap::MAXDUNY; $yy += 2) {
            for ($xx = 0; $xx < DungeonMap::MAXDUNX; $xx += 2) {
                $map->dPiece[$yy][$xx] = 426;
                $map->dPiece[$yy][$xx + 1] = 426;
                $map->dPiece[$yy + 1][$xx] = 426;
                $map->dPiece[$yy + 1][$xx + 1] = 426;
            }
        }

        self::fillSector($map, $assets, 'levels\\towndata\\sector1s.dun', 46, 46);
        self::fillSector($map, $assets, 'levels\\towndata\\sector2s.dun', 46, 0);
        self::fillSector($map, $assets, 'levels\\towndata\\sector3s.dun', 0, 46);
        self::fillSector($map, $assets, 'levels\\towndata\\sector4s.dun', 0, 0);

        // PlaceDunTiles(automap.dun,{0,0}) — dungeon[] only
        if ($assets->has('levels\\towndata\\automap.dun')) {
            self::placeDunTilesMegaOnly($map, $assets, 'levels\\towndata\\automap.dun', 0, 0);
        }

        // Closed warps (IsWarpOpen false)
        $map->dungeon[7][20] = 10; // DX dungeon[20][7] — PHP dungeon is [y][x]
        $map->dungeon[6][20] = 8;
        self::fillTile($map, 48, 20, 320);

        $map->dungeon[30][4] = 8;
        self::fillTile($map, 16, 68, 332);
        self::fillTile($map, 16, 70, 331);

        $map->dungeon[35][15] = 7;
        $map->dungeon[35][16] = 7;
        $map->dungeon[35][17] = 7;
        // Hell closed strip: PickRandomlyAmong({1,2,3,4}) — non-deterministic in ;
        // PHP loadTown uses 1+($x%4). Oracle uses same deterministic stand-in for stable diffs.
        for ($x = 36; $x < 46; $x++) {
            self::fillTile($map, $x, 78, 1 + ($x % 4));
        }

        // Q_PWATER not done → FillTile(60,70,71) in else branch of NOTAVAIL/DONE check:
        // : if active && !done → 342; else 71. Default new game → 71.
        self::fillTile($map, 60, 70, 71);

        self::initTownPieces($map);
    }

    /** FillSector — writes dPiece only. */
    private static function fillSector(DungeonMap $map, AssetStore $assets, string $path, int $xi, int $yy): void
    {
        $raw = $assets->read($path);
        $width = unpack('v', substr($raw, 0, 2))[1];
        $height = unpack('v', substr($raw, 2, 2))[1];
        $tiles = substr($raw, 4);
        for ($j = 0; $j < $height; $j++) {
            $xx = $xi;
            for ($i = 0; $i < $width; $i++) {
                $v1 = $v2 = $v3 = $v4 = 218;
                $tileId = unpack('v', substr($tiles, (($j * $width) + $i) * 2, 2))[1] - 1;
                if ($tileId >= 0 && isset($map->megaTiles[$tileId])) {
                    $m = $map->megaTiles[$tileId];
                    $v1 = (int) $m['micro1'];
                    $v2 = (int) $m['micro2'];
                    $v3 = (int) $m['micro3'];
                    $v4 = (int) $m['micro4'];
                }
                // : dPiece[xx][yy] — PHP store [y][x]
                $map->dPiece[$yy][$xx] = $v1;
                $map->dPiece[$yy][$xx + 1] = $v2;
                $map->dPiece[$yy + 1][$xx] = $v3;
                $map->dPiece[$yy + 1][$xx + 1] = $v4;
                $xx += 2;
            }
            $yy += 2;
        }
    }

    private static function fillTile(DungeonMap $map, int $xx, int $yy, int $t): void
    {
        $tid = $t - 1;
        if ($tid < 0 || !isset($map->megaTiles[$tid])) {
            return;
        }
        $m = $map->megaTiles[$tid];
        $map->dPiece[$yy][$xx] = (int) $m['micro1'];
        $map->dPiece[$yy][$xx + 1] = (int) $m['micro2'];
        $map->dPiece[$yy + 1][$xx] = (int) $m['micro3'];
        $map->dPiece[$yy + 1][$xx + 1] = (int) $m['micro4'];
    }

    /** PlaceDunTiles — dungeon mega grid only. */
    private static function placeDunTilesMegaOnly(DungeonMap $map, AssetStore $assets, string $path, int $ox, int $oy): void
    {
        $raw = $assets->read($path);
        $width = unpack('v', substr($raw, 0, 2))[1];
        $height = unpack('v', substr($raw, 2, 2))[1];
        $tiles = substr($raw, 4);
        for ($j = 0; $j < $height; $j++) {
            for ($i = 0; $i < $width; $i++) {
                $tileId = unpack('v', substr($tiles, (($j * $width) + $i) * 2, 2))[1];
                if ($tileId === 0) {
                    continue;
                }
                // : dungeon[position.x+i][position.y+j] with dungeon[x][y]
                // PHP dungeon is [y][x]
                $dx = $ox + $i;
                $dy = $oy + $j;
                if ($dx >= 0 && $dy >= 0 && $dx < DungeonMap::DMAXX && $dy < DungeonMap::DMAXY) {
                    $map->dungeon[$dy][$dx] = $tileId & 0xFF;
                }
            }
        }
    }

    private static function initTownPieces(DungeonMap $map): void
    {
        $pieceMap = [
            359 => 1, 357 => 2, 128 => 6, 129 => 7, 127 => 8, 116 => 9,
            156 => 10, 157 => 11, 155 => 12, 161 => 13, 159 => 14,
            213 => 15, 211 => 16, 216 => 17, 215 => 18,
        ];
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $piece = (int) ($map->dPiece[$y][$x] ?? 0);
                if (isset($pieceMap[$piece])) {
                    $map->dSpecial[$y][$x] = $pieceMap[$piece];
                }
            }
        }
    }
}
