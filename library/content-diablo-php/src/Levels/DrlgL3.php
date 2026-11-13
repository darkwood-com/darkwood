<?php

declare(strict_types=1);

namespace Diablo\Levels;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Random;

/**
 * Caves CreateL3Dungeon — separate from Cathedral (not a DrlgL1 reuse).
 * PARTIAL port: room/hall mask → mega tiles from l2.til; full FixTiles pending.
 * See NOTICE.md for attribution.
 */
final class DrlgL3
{
    public const FLOOR = 8;
    public const WALL = 1;

    /**
     * L3UP replace megas; search adapted to all-floor (PARTIAL lacks DX edge tiles 7/9/10).
     */
    private const L3UP = [
        'w' => 3, 'h' => 3,
        'search' => [
            [8, 8, 8],
            [8, 8, 8],
            [8, 8, 8],
        ],
        'replace' => [
            [51, 50, 0],
            [48, 49, 0],
            [0, 0, 0],
        ],
    ];
    private const L3DOWN = [
        'w' => 3, 'h' => 3,
        'search' => [
            [8, 8, 8],
            [8, 8, 8],
            [8, 8, 8],
        ],
        'replace' => [
            [0, 47, 0],
            [0, 46, 0],
            [0, 0, 0],
        ],
    ];
    /** L3HOLDWARP — town warp on caves L9 (search adapted to floor 8). */
    private const L3HOLDWARP = [
        'w' => 3, 'h' => 3,
        'search' => [
            [8, 8, 8],
            [8, 8, 8],
            [8, 8, 8],
        ],
        'replace' => [
            [125, 125, 0],
            [125, 125, 0],
            [0, 0, 0],
        ],
    ];
    /** Stalagmite / stalactite decorations — search adapted to FLOOR=8. */
    private const L3TITE1 = [
        'w' => 4, 'h' => 4,
        'search' => [[8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8]],
        'replace' => [[0, 0, 0, 0], [0, 57, 58, 0], [0, 56, 55, 0], [0, 0, 0, 0]],
    ];
    private const L3TITE2 = [
        'w' => 4, 'h' => 4,
        'search' => [[8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8]],
        'replace' => [[0, 0, 0, 0], [0, 61, 62, 0], [0, 60, 59, 0], [0, 0, 0, 0]],
    ];
    private const L3TITE3 = [
        'w' => 4, 'h' => 4,
        'search' => [[8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8], [8, 8, 8, 8]],
        'replace' => [[0, 0, 0, 0], [0, 65, 66, 0], [0, 64, 63, 0], [0, 0, 0, 0]],
    ];
    private const L3TITE8 = [
        'w' => 3, 'h' => 3,
        'search' => [[8, 8, 8], [8, 8, 8], [8, 8, 8]],
        'replace' => [[0, 0, 0], [0, 52, 0], [0, 0, 0]],
    ];
    private const L3TITE9 = [
        'w' => 3, 'h' => 3,
        'search' => [[8, 8, 8], [8, 8, 8], [8, 8, 8]],
        'replace' => [[0, 0, 0], [0, 53, 0], [0, 0, 0]],
    ];
    private const L3TITE10 = [
        'w' => 3, 'h' => 3,
        'search' => [[8, 8, 8], [8, 8, 8], [8, 8, 8]],
        'replace' => [[0, 0, 0], [0, 54, 0], [0, 0, 0]],
    ];
    private const L3TITE11 = [
        'w' => 3, 'h' => 3,
        'search' => [[8, 8, 8], [8, 8, 8], [8, 8, 8]],
        'replace' => [[0, 0, 0], [0, 67, 0], [0, 0, 0]],
    ];
    /** Stalagmite on wall edge — search adapted WALL=1 / FLOOR=8. */
    private const L3TITE12 = [
        'w' => 2, 'h' => 1,
        'search' => [[1, 8]],
        'replace' => [[68, 0]],
    ];
    private const L3TITE13 = [
        'w' => 1, 'h' => 2,
        'search' => [[1], [8]],
        'replace' => [[69], [0]],
    ];

    /**
     * @return array{dungeon:list<list<int>>, dPiece:list<list<int>>, viewX:int, viewY:int, stairUp:array{0:int,1:int}, stairDown:array{0:int,1:int}}
     */
    public function createL3Dungeon(Random $rng, int $rseed, int $dlvl, array $megaTiles): array
    {
        $rng->setRndSeed($rseed);
        $dungeonXY = [];
        for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
            $dungeonXY[$x] = array_fill(0, DungeonMap::DMAXY, 0);
        }
        // Distinct layout: 3×3 chamber grid with halls (not L1 FirstRoom)
        $rooms = [];
        for ($ry = 0; $ry < 3; $ry++) {
            for ($rx = 0; $rx < 3; $rx++) {
                if ($rng->flipCoin(5) && !($rx === 1 && $ry === 1)) {
                    continue;
                }
                $x = 4 + $rx * 11 + $rng->generateRnd(2);
                $y = 4 + $ry * 11 + $rng->generateRnd(2);
                $w = 6 + $rng->generateRnd(3);
                $h = 6 + $rng->generateRnd(3);
                $rooms[] = [$x, $y, $w, $h];
                for ($j = 0; $j < $h; $j++) {
                    for ($i = 0; $i < $w; $i++) {
                        $xx = $x + $i;
                        $yy = $y + $j;
                        if ($xx > 0 && $yy > 0 && $xx < DungeonMap::DMAXX - 1 && $yy < DungeonMap::DMAXY - 1) {
                            $dungeonXY[$xx][$yy] = self::FLOOR;
                        }
                    }
                }
            }
        }
        // Connect rooms with L-shaped halls; door megas on H and V segment midpoints
        for ($i = 1; $i < count($rooms); $i++) {
            [$x0, $y0] = [$rooms[$i - 1][0] + intdiv($rooms[$i - 1][2], 2), $rooms[$i - 1][1] + intdiv($rooms[$i - 1][3], 2)];
            [$x1, $y1] = [$rooms[$i][0] + intdiv($rooms[$i][2], 2), $rooms[$i][1] + intdiv($rooms[$i][3], 2)];
            while ($x0 !== $x1) {
                $dungeonXY[$x0][$y0] = self::FLOOR;
                $x0 += $x1 <=> $x0;
            }
            while ($y0 !== $y1) {
                $dungeonXY[$x0][$y0] = self::FLOOR;
                $y0 += $y1 <=> $y0;
            }
        }
        $this->placeHallDoorMegas($dungeonXY, $rooms);
        // Walls around floors
        for ($y = 1; $y < DungeonMap::DMAXY - 1; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX - 1; $x++) {
                if ($dungeonXY[$x][$y] === self::FLOOR) {
                    continue;
                }
                if ($dungeonXY[$x][$y] === 146 || $dungeonXY[$x][$y] === 147) {
                    continue; // keep hall doors
                }
                $near = false;
                for ($dy = -1; $dy <= 1; $dy++) {
                    for ($dx = -1; $dx <= 1; $dx++) {
                        if ($dungeonXY[$x + $dx][$y + $dy] === self::FLOOR) {
                            $near = true;
                        }
                    }
                }
                if ($near) {
                    $dungeonXY[$x][$y] = self::WALL;
                }
            }
        }
        // Re-apply hall door megas after wall pass
        $this->placeHallDoorMegas($dungeonXY, $rooms);
        [$up, $down, $warp] = $this->placeStairs($rng, $dungeonXY, $dlvl);
        // Cave décor (PlaceMiniSetRandom L3TITE*) — lower % than DX because PARTIAL floors are all mega 8
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE1, 3);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE2, 3);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE3, 3);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE8, 5);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE9, 5);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE10, 5);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE11, 5);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE12, 8);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::L3TITE13, 8);
        // PlaceMiniSetRandom1x1 — floor dirt variants (FLOOR=8 → 25..28 @ 20%)
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 25, 20);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 26, 20);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 27, 20);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 28, 20);
        // Wall face variants (retail search mega 7 → 29..32; PARTIAL WALL=1)
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 29, 25);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 30, 25);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 31, 25);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 32, 25);
        // Soft wall shadows onto floor (also after wall variants)
        $this->applyShadowsPatterns($dungeonXY);
        $dPiece = $this->pass3($dungeonXY, $megaTiles);
        $dungeonYX = [];
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                $dungeonYX[$y][$x] = $dungeonXY[$x][$y];
            }
        }
        $dPieceYX = [];
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $dPieceYX[$y][$x] = $dPiece[$x][$y] ?? 0;
            }
        }

        return [
            'dungeon' => $dungeonYX,
            'dPiece' => $dPieceYX,
            // PlaceCaveStairs ENTRY_MAIN: megaToWorld + {1,3}
            'viewX' => 16 + $up[0] * 2 + 1,
            'viewY' => 16 + $up[1] * 2 + 3,
            'stairUp' => $up,
            'stairDown' => $down,
            'stairWarp' => $warp,
            'source' => 'CreateL3Dungeon/PARTIAL+doors+stairs+tites+walltite+floorvar+wallvar+shadow',
        ];
    }

    /**
     * Soft ApplyShadowsPatterns — floor megas west of WALL / wall-variants get tint 39/40.
     *
     * @param list<list<int>> $dungeonXY
     */
    private function applyShadowsPatterns(array &$dungeonXY): void
    {
        $casters = [self::WALL, 29, 30, 31, 32, 68, 69];
        for ($y = 1; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX; $x++) {
                if (!in_array($dungeonXY[$x][$y] ?? 0, $casters, true)) {
                    continue;
                }
                if (($dungeonXY[$x - 1][$y] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y] = 39;
                }
                if (($dungeonXY[$x - 1][$y - 1] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y - 1] = 40;
                }
            }
        }
    }

    /**
     * @param list<list<int>> $dungeonXY
     * @return array{0:array{0:int,1:int},1:array{0:int,1:int},2:?array{0:int,1:int}}
     */
    private function placeStairs(Random $rng, array &$dungeonXY, int $dlvl): array
    {
        $up = $this->placeMiniSet($rng, $dungeonXY, self::L3UP);
        $down = $this->placeMiniSet($rng, $dungeonXY, self::L3DOWN);
        $warp = null;
        if ($dlvl === 9) {
            $warp = $this->placeMiniSet($rng, $dungeonXY, self::L3HOLDWARP);
        }
        if ($up === null || $down === null) {
            $floors = [];
            for ($y = 2; $y < DungeonMap::DMAXY - 2; $y++) {
                for ($x = 2; $x < DungeonMap::DMAXX - 2; $x++) {
                    if ($dungeonXY[$x][$y] === self::FLOOR) {
                        $floors[] = [$x, $y];
                    }
                }
            }
            if ($floors === []) {
                throw new \RuntimeException('DrlgL3 produced no floors for stairs');
            }
            $up ??= $floors[$rng->generateRnd(count($floors))];
            if ($down === null) {
                $down = $floors[0];
                $best = -1;
                foreach ($floors as $f) {
                    $d = abs($f[0] - $up[0]) + abs($f[1] - $up[1]);
                    if ($d > $best) {
                        $best = $d;
                        $down = $f;
                    }
                }
            }
        }

        return [$up, $down, $warp];
    }

    /**
     * PlaceMiniSetRandom — scatter décor with rndper% chance per match.
     *
     * @param list<list<int>> $dungeonXY
     * @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset
     */
    private function placeMiniSetRandom(Random $rng, array &$dungeonXY, array $miniset, int $rndper): void
    {
        $sw = $miniset['w'];
        $sh = $miniset['h'];
        for ($sy = 0; $sy < DungeonMap::DMAXY - $sh; $sy++) {
            for ($sx = 0; $sx < DungeonMap::DMAXX - $sw; $sx++) {
                $ok = true;
                for ($yy = 0; $yy < $sh && $ok; $yy++) {
                    for ($xx = 0; $xx < $sw; $xx++) {
                        $s = $miniset['search'][$yy][$xx];
                        if ($s !== 0 && ($dungeonXY[$xx + $sx][$yy + $sy] ?? -1) !== $s) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if (!$ok || $rng->generateRnd(100) >= $rndper) {
                    continue;
                }
                for ($y = 0; $y < $sh; $y++) {
                    for ($x = 0; $x < $sw; $x++) {
                        $r = $miniset['replace'][$y][$x];
                        if ($r !== 0) {
                            $dungeonXY[$x + $sx][$y + $sy] = $r;
                        }
                    }
                }
            }
        }
    }

    /**
     * PlaceMiniSetRandom1x1 — single-tile search→replace at rndper%.
     *
     * @param list<list<int>> $dungeonXY
     */
    private function placeMiniSetRandom1x1(Random $rng, array &$dungeonXY, int $search, int $replace, int $rndper): void
    {
        $this->placeMiniSetRandom($rng, $dungeonXY, [
            'w' => 1,
            'h' => 1,
            'search' => [[$search]],
            'replace' => [[$replace]],
        ], $rndper);
    }

    /**
     * @param list<list<int>> $dungeonXY
     * @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset
     * @return array{0:int,1:int}|null
     */
    private function placeMiniSet(Random $rng, array &$dungeonXY, array $miniset): ?array
    {
        $sw = $miniset['w'];
        $sh = $miniset['h'];
        $tries = DungeonMap::DMAXX * DungeonMap::DMAXY;
        $px = $rng->generateRnd(max(1, DungeonMap::DMAXX - $sw));
        $py = $rng->generateRnd(max(1, DungeonMap::DMAXY - $sh));
        for ($i = 0; $i < $tries; $i++, $px++) {
            if ($px >= DungeonMap::DMAXX - $sw) {
                $px = 0;
                $py++;
                if ($py >= DungeonMap::DMAXY - $sh) {
                    $py = 0;
                }
            }
            $ok = true;
            for ($yy = 0; $yy < $sh && $ok; $yy++) {
                for ($xx = 0; $xx < $sw; $xx++) {
                    $s = $miniset['search'][$yy][$xx];
                    if ($s !== 0 && ($dungeonXY[$xx + $px][$yy + $py] ?? -1) !== $s) {
                        $ok = false;
                        break;
                    }
                }
            }
            if (!$ok) {
                continue;
            }
            for ($y = 0; $y < $sh; $y++) {
                for ($x = 0; $x < $sw; $x++) {
                    $r = $miniset['replace'][$y][$x];
                    if ($r !== 0) {
                        $dungeonXY[$x + $px][$y + $py] = $r;
                    }
                }
            }

            return [$px, $py];
        }

        return null;
    }

    /**
     * Place L3 door megas at midpoints of each hall segment.
     * Mega 146 → piece 530 (OBJ_L3LDOOR); mega 147 → piece 533 (OBJ_L3RDOOR).
     *
     * @param list<list<int>> $dungeonXY
     * @param list<array{0:int,1:int,2:int,3:int}> $rooms
     */
    private function placeHallDoorMegas(array &$dungeonXY, array $rooms): void
    {
        for ($i = 1; $i < count($rooms); $i++) {
            [$x0, $y0] = [$rooms[$i - 1][0] + intdiv($rooms[$i - 1][2], 2), $rooms[$i - 1][1] + intdiv($rooms[$i - 1][3], 2)];
            [$x1, $y1] = [$rooms[$i][0] + intdiv($rooms[$i][2], 2), $rooms[$i][1] + intdiv($rooms[$i][3], 2)];
            $hPath = [];
            $cx = $x0;
            $cy = $y0;
            while ($cx !== $x1) {
                $hPath[] = [$cx, $cy];
                $cx += $x1 <=> $cx;
            }
            if (count($hPath) >= 2) {
                [$mx, $my] = $hPath[intdiv(count($hPath), 2)];
                $dungeonXY[$mx][$my] = 147; // E–W → R-door
            }
            $vPath = [];
            while ($cy !== $y1) {
                $vPath[] = [$cx, $cy];
                $cy += $y1 <=> $cy;
            }
            if (count($vPath) >= 2) {
                [$mx, $my] = $vPath[intdiv(count($vPath), 2)];
                $dungeonXY[$mx][$my] = 146; // N–S → L-door
            }
        }
    }

    /** @param list<list<int>> $dungeonXY @param list<array{micro1:int,micro2:int,micro3:int,micro4:int}> $megaTiles */
    private function pass3(array $dungeonXY, array $megaTiles): array
    {
        $dPiece = [];
        for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
            $dPiece[$x] = array_fill(0, DungeonMap::MAXDUNY, 0);
        }
        $yy = 16;
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            $xx = 16;
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                $tileId = max(0, ($dungeonXY[$i][$j] ?? 1) - 1);
                $m = $megaTiles[$tileId] ?? ($megaTiles[0] ?? ['micro1' => 0, 'micro2' => 0, 'micro3' => 0, 'micro4' => 0]);
                $dPiece[$xx][$yy] = $m['micro1'];
                $dPiece[$xx + 1][$yy] = $m['micro2'];
                $dPiece[$xx][$yy + 1] = $m['micro3'];
                $dPiece[$xx + 1][$yy + 1] = $m['micro4'];
                $xx += 2;
            }
            $yy += 2;
        }

        return $dPiece;
    }
}
