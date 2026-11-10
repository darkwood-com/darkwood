<?php

declare(strict_types=1);

namespace Diablo\Levels;

use Diablo\Engine\Assets\AssetStore;
use Diablo\Engine\Random;

/**
 * Hell CreateL4Dungeon — separate from Cathedral (not a DrlgL1 reuse).
 * PARTIAL port: room/hall mask → mega tiles from l2.til; full FixTiles pending.
 * See NOTICE.md for attribution.
 */
final class DrlgL4
{
    public const FLOOR = 6;
    public const WALL = 2;

    /** L4USTAIRS — DX Miniset {4×5} floor→stair megas */
    private const L4USTAIRS = [
        'w' => 4, 'h' => 5,
        'search' => [
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
        ],
        'replace' => [
            [0, 0, 0, 0],
            [36, 38, 35, 0],
            [37, 34, 33, 32],
            [0, 0, 31, 0],
            [0, 0, 0, 0],
        ],
    ];
    /** L4DSTAIRS — DX Miniset {5×5} */
    private const L4DSTAIRS = [
        'w' => 5, 'h' => 5,
        'search' => [
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
        ],
        'replace' => [
            [0, 0, 0, 0, 0],
            [0, 0, 45, 41, 0],
            [0, 44, 43, 40, 0],
            [0, 46, 42, 39, 0],
            [0, 0, 0, 0, 0],
        ],
    ];
    /** L4TWARP — town warp on hell L13 */
    private const L4TWARP = [
        'w' => 4, 'h' => 5,
        'search' => [
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
            [6, 6, 6, 6],
        ],
        'replace' => [
            [0, 0, 0, 0],
            [134, 136, 133, 0],
            [135, 132, 131, 130],
            [0, 0, 129, 0],
            [0, 0, 0, 0],
        ],
    ];
    /** L4PENTA2 — Diablo gate on L15 */
    private const L4PENTA2 = [
        'w' => 5, 'h' => 5,
        'search' => [
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
            [6, 6, 6, 6, 6],
        ],
        'replace' => [
            [0, 0, 0, 0, 0],
            [0, 107, 109, 112, 0],
            [0, 108, 111, 114, 0],
            [0, 110, 113, 115, 0],
            [0, 0, 0, 0, 0],
        ],
    ];

    /**
     * @return array{dungeon:list<list<int>>, dPiece:list<list<int>>, viewX:int, viewY:int, stairUp:array{0:int,1:int}, stairDown:array{0:int,1:int}}
     */
    public function createL4Dungeon(Random $rng, int $rseed, int $dlvl, array $megaTiles): array
    {
        $rng->setRndSeed($rseed);
        $dungeonXY = [];
        for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
            $dungeonXY[$x] = array_fill(0, DungeonMap::DMAXY, 0);
        }
        // Distinct layout: 4×4 chamber grid with halls (not L1 FirstRoom)
        $rooms = [];
        for ($ry = 0; $ry < 4; $ry++) {
            for ($rx = 0; $rx < 4; $rx++) {
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
        // Connect rooms with halls
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
        // Walls around floors
        for ($y = 1; $y < DungeonMap::DMAXY - 1; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX - 1; $x++) {
                if ($dungeonXY[$x][$y] === self::FLOOR) {
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
        // Soft FixTilesPatterns (WALL=2 / FLOOR=6 subset)
        $this->fixTilesPatterns($dungeonXY);
        // Substitution (partial) — L4BTYPES-style wall/floor variants + lava 95/96/97
        $this->substitution($rng, $dungeonXY);
        // Wall face variants (L4BTYPES indices with base type 1/2)
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 1, 8);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 3, 6);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 4, 6);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 7, 5);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 8, 5);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 9, 5);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 15, 4);
        $this->placeMiniSetRandom1x1($rng, $dungeonXY, self::WALL, 16, 4);
        // ApplyShadowsPatterns — floor shadow megas west of walls
        $this->applyShadowsPatterns($dungeonXY);
        [$up, $down, $warp] = $this->placeStairs($rng, $dungeonXY, $dlvl);
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
            // PlaceStairs ENTRY_MAIN: megaToWorld + {6,6}
            'viewX' => 16 + $up[0] * 2 + 6,
            'viewY' => 16 + $up[1] * 2 + 6,
            'stairUp' => $up,
            'stairDown' => $down,
            'stairWarp' => $warp,
            'source' => 'CreateL4Dungeon/PARTIAL+fixtiles+subst+wallvar+shadow+stairs',
        ];
    }

    /**
     * Soft FixTilesPatterns — edge polish for WALL=2 / FLOOR=6 PARTIAL layouts.
     *
     * @param list<list<int>> $dungeonXY
     */
    private function fixTilesPatterns(array &$dungeonXY): void
    {
        for ($y = 0; $y < DungeonMap::DMAXY - 1; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX - 1; $x++) {
                $t = $dungeonXY[$x][$y] ?? 0;
                $e = $dungeonXY[$x + 1][$y] ?? 0;
                $s = $dungeonXY[$x][$y + 1] ?? 0;
                // WALL east of FLOOR → inner lip mega 5
                if ($t === self::WALL && $e === self::FLOOR) {
                    $dungeonXY[$x + 1][$y] = 5;
                }
                // WALL south of FLOOR → mega 14 family edge
                if ($t === self::FLOOR && $s === self::WALL) {
                    $dungeonXY[$x][$y + 1] = 14;
                }
            }
        }
        // Second pass — tame double-walls into corner pieces
        for ($y = 0; $y < DungeonMap::DMAXY - 1; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX - 1; $x++) {
                $t = $dungeonXY[$x][$y] ?? 0;
                $e = $dungeonXY[$x + 1][$y] ?? 0;
                if ($t === self::WALL && $e === 5) {
                    // keep lip
                    continue;
                }
                if ($t === 14 && $e === self::FLOOR) {
                    $dungeonXY[$x + 1][$y] = 15;
                }
                if ($t === self::FLOOR && ($dungeonXY[$x][$y + 1] ?? 0) === 14) {
                    // already set
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
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                if (($dungeonXY[$x][$y] ?? 0) !== $search) {
                    continue;
                }
                if ($rng->generateRnd(100) >= $rndper) {
                    continue;
                }
                $dungeonXY[$x][$y] = $replace;
            }
        }
    }

    /**
     * Substitution — ~10% lava floors + occasional floor tile family swaps (49/50/51).
     *
     * @param list<list<int>> $dungeonXY
     */
    private function substitution(Random $rng, array &$dungeonXY): void
    {
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                if ($dungeonXY[$x][$y] !== self::FLOOR) {
                    continue;
                }
                if ($rng->flipCoin(10)) {
                    $dungeonXY[$x][$y] = [95, 96, 97][$rng->generateRnd(3)];
                } elseif ($rng->flipCoin(8)) {
                    $dungeonXY[$x][$y] = [49, 50, 51][$rng->generateRnd(3)];
                }
            }
        }
    }

    /**
     * ApplyShadowsPatterns — cast floor shadow tiles (47/48) west of wall megas.
     * Retail keys 3/4/8/15; PARTIAL also treats WALL=2 and soft FixTiles edges.
     *
     * @param list<list<int>> $dungeonXY
     */
    private function applyShadowsPatterns(array &$dungeonXY): void
    {
        $casters = [self::WALL, 1, 3, 4, 7, 8, 9, 14, 15, 16];
        for ($y = 1; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX; $x++) {
                $t = $dungeonXY[$x][$y] ?? 0;
                if (!in_array($t, $casters, true)) {
                    continue;
                }
                if (($dungeonXY[$x - 1][$y] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y] = 47;
                }
                if (($dungeonXY[$x - 1][$y - 1] ?? 0) === self::FLOOR) {
                    $dungeonXY[$x - 1][$y - 1] = 48;
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
        $up = $this->placeMiniSet($rng, $dungeonXY, self::L4USTAIRS);
        $down = null;
        $warp = null;
        if ($dlvl === 15) {
            // Hell gate replaces down stairs
            $down = $this->placeMiniSet($rng, $dungeonXY, self::L4PENTA2);
        } elseif ($dlvl < 15) {
            $down = $this->placeMiniSet($rng, $dungeonXY, self::L4DSTAIRS);
        }
        if ($dlvl === 13) {
            $warp = $this->placeMiniSet($rng, $dungeonXY, self::L4TWARP);
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
                throw new \RuntimeException('DrlgL4 produced no floors for stairs');
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
