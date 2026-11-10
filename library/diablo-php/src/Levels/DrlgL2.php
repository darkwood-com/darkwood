<?php

declare(strict_types=1);

namespace Diablo\Levels;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Random;

/**
 * Catacombs CreateL2Dungeon — separate from Cathedral (not a DrlgL1 reuse).
 * PARTIAL port: room/hall mask → mega tiles from l2.til; full FixTiles pending.
 * See NOTICE.md for attribution.
 */
final class DrlgL2
{
    public const FLOOR = 3;
    public const WALL = 16;

    /** USTAIRS — DX Miniset {4×4} floor→stair megas */
    private const USTAIRS = [
        'w' => 4, 'h' => 4,
        'search' => [
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
        ],
        'replace' => [
            [0, 0, 0, 0],
            [0, 72, 77, 0],
            [0, 76, 0, 0],
            [0, 0, 0, 0],
        ],
    ];
    /** DSTAIRS — DX Miniset {4×4} */
    private const DSTAIRS = [
        'w' => 4, 'h' => 4,
        'search' => [
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
        ],
        'replace' => [
            [0, 0, 0, 0],
            [0, 48, 71, 0],
            [0, 50, 78, 0],
            [0, 0, 0, 0],
        ],
    ];
    /** WARPSTAIRS — town warp on catacombs L5 */
    private const WARPSTAIRS = [
        'w' => 4, 'h' => 4,
        'search' => [
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
            [3, 3, 3, 3],
        ],
        'replace' => [
            [0, 0, 0, 0],
            [0, 158, 160, 0],
            [0, 159, 0, 0],
            [0, 0, 0, 0],
        ],
    ];
    /**
     * VARCH31/32 — vertical arches against WALL=16 (search softened: intermediate megas → floor).
     */
    private const VARCH31 = [
        'w' => 2, 'h' => 4,
        'search' => [[3, 0], [3, 3], [3, 3], [0, 16]],
        'replace' => [[48, 0], [51, 39], [47, 44], [0, 0]],
    ];
    private const VARCH32 = [
        'w' => 2, 'h' => 4,
        'search' => [[3, 0], [3, 3], [3, 3], [0, 16]],
        'replace' => [[48, 0], [51, 39], [47, 44], [0, 0]],
    ];
    /** Alternate vertical arch column (VARCH1-style replace art). */
    private const VARCH_ALT = [
        'w' => 2, 'h' => 4,
        'search' => [[3, 0], [3, 3], [3, 3], [16, 16]],
        'replace' => [[48, 0], [51, 39], [47, 44], [0, 0]],
    ];
    /**
     * VARCH33–36 softened — west-entrance arches (retail 2/8/4/7 → FLOOR/WALL).
     * Replace art uses 142/51/42 pillar set when present in l2.til.
     */
    private const VARCH_WEST = [
        'w' => 2, 'h' => 4,
        'search' => [[3, 0], [3, 3], [3, 3], [0, 16]],
        'replace' => [[142, 0], [51, 42], [47, 44], [0, 0]],
    ];
    private const VARCH_WEST2 = [
        'w' => 2, 'h' => 4,
        'search' => [[3, 0], [3, 3], [3, 16], [0, 16]],
        'replace' => [[142, 0], [51, 42], [47, 44], [0, 0]],
    ];
    /** Extra horizontal arch along longer floor runs. */
    private const HARCH3 = [
        'w' => 4, 'h' => 2,
        'search' => [[3, 3, 3, 0], [3, 3, 3, 16]],
        'replace' => [[49, 46, 49, 0], [40, 45, 40, 0]],
    ];
    /**
     * HARCH6-style horizontal arch (retail uses mega 2/5; PARTIAL → floor against WALL=16).
     * Replace megas 49/46/40/45 match l2.til arch art.
     */
    private const HARCH1 = [
        'w' => 3, 'h' => 2,
        'search' => [[3, 3, 0], [3, 3, 16]],
        'replace' => [[49, 46, 0], [40, 45, 0]],
    ];
    /** Mirror: arch opening north of a south wall. */
    private const HARCH2 = [
        'w' => 3, 'h' => 2,
        'search' => [[3, 3, 16], [3, 3, 0]],
        'replace' => [[40, 45, 0], [49, 46, 0]],
    ];
    /** Horizontal arch abutting an E–W door mega (5). */
    private const HARCH_DOOR = [
        'w' => 3, 'h' => 2,
        'search' => [[3, 3, 0], [3, 5, 16]],
        'replace' => [[49, 46, 0], [40, 45, 0]],
    ];
    /** Horizontal arch abutting N–S door mega (4). */
    private const HARCH_DOOR2 = [
        'w' => 3, 'h' => 2,
        'search' => [[3, 3, 0], [3, 4, 16]],
        'replace' => [[49, 46, 0], [40, 45, 0]],
    ];
    /**
     * CTRDOOR — nudge door megas one tile off abutting walls (retail CTRDOOR* softened).
     * Mega 5 = E–W passage (floors left/right); mega 4 = N–S passage (floors up/down).
     */
    private const CTRDOOR_5_S = [
        'w' => 3, 'h' => 3,
        'search' => [[3, 5, 3], [0, 3, 0], [0, 16, 0]],
        'replace' => [[0, 3, 0], [0, 5, 0], [0, 0, 0]],
    ];
    private const CTRDOOR_5_N = [
        'w' => 3, 'h' => 3,
        'search' => [[0, 16, 0], [0, 3, 0], [3, 5, 3]],
        'replace' => [[0, 0, 0], [0, 5, 0], [0, 3, 0]],
    ];
    private const CTRDOOR_4_E = [
        'w' => 3, 'h' => 3,
        'search' => [[3, 0, 0], [4, 3, 16], [3, 0, 0]],
        'replace' => [[0, 0, 0], [3, 4, 0], [0, 0, 0]],
    ];
    private const CTRDOOR_4_W = [
        'w' => 3, 'h' => 3,
        'search' => [[0, 0, 3], [16, 3, 4], [0, 0, 3]],
        'replace' => [[0, 0, 0], [0, 4, 3], [0, 0, 0]],
    ];

    /**
     * @return array{dungeon:list<list<int>>, dPiece:list<list<int>>, viewX:int, viewY:int, stairUp:array{0:int,1:int}, stairDown:array{0:int,1:int}}
     */
    public function createL2Dungeon(Random $rng, int $rseed, int $dlvl, array $megaTiles): array
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
                if ($dungeonXY[$x][$y] === self::FLOOR || $dungeonXY[$x][$y] === 4 || $dungeonXY[$x][$y] === 5) {
                    continue;
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
        // Re-apply hall door megas (wall pass can still overwrite midpoints)
        $this->placeHallDoorMegas($dungeonXY, $rooms);
        // Extra doors on thin walls between floors
        $this->placeDoorMegs($dungeonXY);
        // CTRDOOR — pull doors off abutting walls (PlaceMiniSetRandom @ 100%)
        $this->placeMiniSetRandom($rng, $dungeonXY, self::CTRDOOR_5_S, 100);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::CTRDOOR_5_N, 100);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::CTRDOOR_4_E, 100);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::CTRDOOR_4_W, 100);
        [$up, $down, $warp] = $this->placeStairs($rng, $dungeonXY, $dlvl);
        // Catacombs décor arches (PlaceMiniSetRandom VARCH*/HARCH*)
        $this->placeMiniSetRandom($rng, $dungeonXY, self::VARCH31, 12);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::VARCH32, 12);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::VARCH_ALT, 10);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::VARCH_WEST, 10);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::VARCH_WEST2, 8);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::HARCH1, 12);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::HARCH2, 12);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::HARCH3, 8);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::HARCH_DOOR, 18);
        $this->placeMiniSetRandom($rng, $dungeonXY, self::HARCH_DOOR2, 15);
        // PlaceMiniSetRandom1x1 — floor dirt variants (search FLOOR=3 → 80/81/82)
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 80, 10);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 81, 10);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::FLOOR, 82, 10);
        // Wall face variants (retail search mega 2 → 84/85/86; PARTIAL WALL=16)
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 84, 10);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 85, 10);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 86, 10);
        // ApplyShadowsPatterns (SPATSL2 soft port for PARTIAL WALL=16)
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
            // PlaceStairs ENTRY_MAIN: megaToWorld + {5,4}
            'viewX' => 16 + $up[0] * 2 + 5,
            'viewY' => 16 + $up[1] * 2 + 4,
            'stairUp' => $up,
            'stairDown' => $down,
            'stairWarp' => $warp,
            'source' => 'CreateL2Dungeon/PARTIAL+doors+ctrdoor+stairs+arch+varchwest+floorvar+wallvar+shadow',
        ];
    }

    /**
     * ApplyShadowsPatterns — SPATSL2 soft port.
     * Retail maps via BSTYPESL2; PARTIAL WALL=16 has btype 0, so cast shadows from WALL onto FLOOR.
     * Shadow megas 48 / 50 match SPATSL2 nv1 / nv3.
     *
     * @param list<list<int>> $dungeonXY
     */
    private function applyShadowsPatterns(array &$dungeonXY): void
    {
        for ($y = 1; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX; $x++) {
                $t = $dungeonXY[$x][$y] ?? 0;
                // Retail strig types 6/9 (pillars etc.) + PARTIAL WALL
                if ($t !== self::WALL && $t !== 6 && $t !== 9) {
                    continue;
                }
                if (($dungeonXY[$x - 1][$y] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y] = 50;
                }
                if (($dungeonXY[$x - 1][$y - 1] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y - 1] = 48;
                }
            }
        }
    }

    /**
     * PlaceStairs — USTAIRS then DSTAIRS; WARPSTAIRS on dlvl 5.
     *
     * @param list<list<int>> $dungeonXY
     * @return array{0:array{0:int,1:int},1:array{0:int,1:int},2:?array{0:int,1:int}}
     */
    private function placeStairs(Random $rng, array &$dungeonXY, int $dlvl): array
    {
        $up = $this->placeMiniSet($rng, $dungeonXY, self::USTAIRS);
        $down = $this->placeMiniSet($rng, $dungeonXY, self::DSTAIRS);
        $warp = null;
        if ($dlvl === 5) {
            $warp = $this->placeMiniSet($rng, $dungeonXY, self::WARPSTAIRS);
        }
        if ($up === null || $down === null) {
            // Fallback: any floor cell (pre-stair behavior)
            $floors = [];
            for ($y = 2; $y < DungeonMap::DMAXY - 2; $y++) {
                for ($x = 2; $x < DungeonMap::DMAXX - 2; $x++) {
                    if ($dungeonXY[$x][$y] === self::FLOOR) {
                        $floors[] = [$x, $y];
                    }
                }
            }
            if ($floors === []) {
                throw new \RuntimeException('DrlgL2 produced no floors for stairs');
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
            if (!$this->minisetMatches($dungeonXY, $miniset, $px, $py)) {
                continue;
            }
            $this->minisetPlace($dungeonXY, $miniset, $px, $py);

            return [$px, $py];
        }

        return null;
    }

    /**
     * @param list<list<int>> $dungeonXY
     * @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset
     */
    private function minisetMatches(array $dungeonXY, array $miniset, int $px, int $py): bool
    {
        for ($yy = 0; $yy < $miniset['h']; $yy++) {
            for ($xx = 0; $xx < $miniset['w']; $xx++) {
                $s = $miniset['search'][$yy][$xx];
                if ($s !== 0 && ($dungeonXY[$xx + $px][$yy + $py] ?? -1) !== $s) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param list<list<int>> $dungeonXY
     * @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset
     */
    private function minisetPlace(array &$dungeonXY, array $miniset, int $px, int $py): void
    {
        for ($y = 0; $y < $miniset['h']; $y++) {
            for ($x = 0; $x < $miniset['w']; $x++) {
                $r = $miniset['replace'][$y][$x];
                if ($r === 0) {
                    continue;
                }
                $dungeonXY[$x + $px][$y + $py] = $r;
            }
        }
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
                if (!$this->minisetMatches($dungeonXY, $miniset, $sx, $sy)) {
                    continue;
                }
                if ($rng->generateRnd(100) >= $rndper) {
                    continue;
                }
                $this->minisetPlace($dungeonXY, $miniset, $sx, $sy);
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
     * Place L2 door megas at midpoints of each hall segment.
     * Mega 4 → pieces 12–15 (OBJ_L2LDOOR on 12); mega 5 → 16–19 (OBJ_L2RDOOR on 16).
     * L-shaped halls are H then V — a single path midpoint often lands only on E–W (R doors).
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
                $dungeonXY[$mx][$my] = 5; // E–W → R-door
            }
            $vPath = [];
            while ($cy !== $y1) {
                $vPath[] = [$cx, $cy];
                $cy += $y1 <=> $cy;
            }
            if (count($vPath) >= 2) {
                [$mx, $my] = $vPath[intdiv(count($vPath), 2)];
                $dungeonXY[$mx][$my] = 4; // N–S → L-door
            }
        }
    }

    /**
     * Punch door megas into walls between floors (L2 mega 4 = L-door, 5 = R-door).
     * @param list<list<int>> $dungeonXY
     */
    private function placeDoorMegs(array &$dungeonXY): void
    {
        for ($y = 2; $y < DungeonMap::DMAXY - 2; $y++) {
            for ($x = 2; $x < DungeonMap::DMAXX - 2; $x++) {
                if ($dungeonXY[$x][$y] !== self::WALL) {
                    continue;
                }
                $n = $dungeonXY[$x][$y - 1] === self::FLOOR;
                $s = $dungeonXY[$x][$y + 1] === self::FLOOR;
                $w = $dungeonXY[$x - 1][$y] === self::FLOOR;
                $e = $dungeonXY[$x + 1][$y] === self::FLOOR;
                if ($n && $s && !$w && !$e) {
                    $dungeonXY[$x][$y] = 4; // mega → piece 12 (OBJ_L2LDOOR)
                } elseif ($w && $e && !$n && !$s) {
                    $dungeonXY[$x][$y] = 5; // mega → piece 16 (OBJ_L2RDOOR)
                }
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
