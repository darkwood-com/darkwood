<?php

declare(strict_types=1);

namespace Diablo\Levels;

use Diablo\Engine\Random;

/**
 * Cathedral L1 dungeon generation.
 * (non-crypt, quest setpieces deferred). Status: SOURCE_PORTED core path; quests stubbed.
 *
 * Call graph:
 * CreateL5Dungeon → GenerateLevel → FirstRoom/FindArea → InitDungeonFlags → MakeDmt
 * → FillChambers → FixTilesPatterns → AddWall → FloodTransparencyValues → PlaceCathedralStairs
 * → FixTransparency → FixDirtTiles → FixCornerTiles → Substitution → ApplyShadowsPatterns
 * → PlaceMiniSet(LAMPS)×N → FillFloor → Pass3 (DRLG_LPass3 + InitDungeonPieces)
 */
final class DrlgL1
{
    public const VWALL = 1;
    public const HWALL = 2;
    public const CORNER = 3;
    public const DWALL = 4;
    public const DARCH = 5;
    public const VWALL_END = 6;
    public const HWALL_END = 7;
    public const HARCH_END = 8;
    public const VARCH_END = 9;
    public const HARCH_VWALL = 10;
    public const VARCH = 11;
    public const HARCH = 12;
    public const FLOOR = 13;
    public const HWALL_VARCH = 14;
    public const PILLAR = 15;
    public const VCORNER = 16;
    public const HCORNER = 17;
    public const DIRT_HWALL = 18;
    public const DIRT_VWALL = 19;
    public const VDIRT_CORNER = 20;
    public const HDIRT_CORNER = 21;
    public const DIRT = 22;
    public const DIRT_HWALL_END = 23;
    public const DIRT_VWALL_END = 24;
    public const VDOOR = 25;
    public const HDOOR = 26;
    public const HFENCE_VWALL = 27;
    public const HDOOR_VDOOR = 28;
    public const DFENCE = 29;
    public const VDOOR_END = 30;
    public const HDOOR_END = 31;
    public const VFENCE_END = 32;
    public const VFENCE = 35;
    public const HFENCE = 36;
    public const HWALL_VFENCE = 37;
    public const HARCH_VFENCE = 38;
    public const HARCH_VDOOR = 39;
    public const ENTRANCE_STAIRS = 64;
    public const VWALL2 = 79;
    public const HWALL2 = 80;
    public const VWALL_END2 = 84;
    public const VWALL4 = 89;
    public const VWALL5 = 90;
    public const HWALL4 = 91;
    public const HWALL5 = 92;
    public const VWALL8 = 100;
    public const FLOOR12 = 139;
    public const FLOOR13 = 140;
    public const FLOOR14 = 141;
    public const FLOOR15 = 142;
    public const FLOOR16 = 143;
    public const FLOOR17 = 144;
    public const FLOOR18 = 145;
    public const VWALL17 = 146;
    public const VARCH5 = 147;
    public const HWALL_SHADOW = 148;
    public const HARCH_SHADOW = 149;
    public const FLOOR19 = 150;
    public const FLOOR20 = 151;
    public const FLOOR21 = 152;
    public const HARCH_SHADOW2 = 153;
    public const HWALL_SHADOW2 = 154;
    public const FLOOR22 = 162;
    public const FLOOR23 = 163;
    public const DIRT_HWALL2 = 199;
    public const DIRT_VWALL2 = 200;
    public const DIRT_CORNER2 = 202;
    public const DIRT_HWALL_END2 = 204;
    public const DIRT_VWALL_END2 = 205;

    /** @var list<list<bool>> mask[y][x] */
    private array $mask = [];
    /** @var list<list<int>> dungeon[x][y] */
    private array $dungeonXY = [];
    /** @var list<list<bool>> */
    private array $protected = [];
    /** @var list<list<bool>> */
    private array $chamber = [];
    /** @var list<list<int>> dTransVal[x][y] */
    private array $dTransVal = [];
    /** @var list<list<int>> dSpecial[x][y] */
    private array $dSpecial = [];
    private int $transVal = 1;

    private bool $verticalLayout = false;
    private bool $hasChamber1 = true;
    private bool $hasChamber2 = true;
    private bool $hasChamber3 = true;

    public int $stairUpX = 0;
    public int $stairUpY = 0;
    public int $stairDownX = 0;
    public int $stairDownY = 0;
    public int $viewX = 0;
    public int $viewY = 0;

    /** STAIRSUP search/replace [y][x] */
    private const STAIRSUP = [
        'w' => 4, 'h' => 4,
        'search' => [[13, 13, 13, 13], [2, 2, 2, 2], [13, 13, 13, 13], [13, 13, 13, 13]],
        'replace' => [[0, 66, 6, 0], [63, 64, 65, 0], [0, 67, 68, 0], [0, 0, 0, 0]],
    ];
    private const STAIRSDOWN = [
        'w' => 4, 'h' => 3,
        'search' => [[13, 13, 13, 13], [13, 13, 13, 13], [13, 13, 13, 13]],
        'replace' => [[62, 57, 58, 0], [61, 59, 60, 0], [0, 0, 0, 0]],
    ];
    private const LAMPS = [
        'w' => 2, 'h' => 2,
        'search' => [[13, 0], [13, 13]],
        'replace' => [[129, 0], [130, 128]],
    ];

    /**
     * @param list<array{micro1:int,micro2:int,micro3:int,micro4:int}> $megaTiles
     * @return array{dungeon:list<list<int>>, dPiece:list<list<int>>, dTransVal:list<list<int>>, dSpecial:list<list<int>>, viewX:int, viewY:int, stairUp:array{0:int,1:int}, stairDown:array{0:int,1:int}, attempts:int, area:int}
     */
    public function createL5Dungeon(Random $rng, int $rseed, int $dlvl, array $megaTiles, int $entry = 0): array
    {
        $rng->setRndSeed($rseed);
        $this->generateLevel($rng, $dlvl, $entry);
        $dPiece = $this->drlgLPass3($megaTiles);
        $this->initDungeonPieces($dPiece);

        $dungeonYX = [];
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            $dungeonYX[$y] = [];
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                $dungeonYX[$y][$x] = $this->dungeonXY[$x][$y] ?? self::DIRT;
            }
        }
        $dPieceYX = [];
        $dTransYX = [];
        $dSpecialYX = [];
        for ($y = 0; $y < DungeonMap::MAXDUNY; $y++) {
            $dPieceYX[$y] = [];
            $dTransYX[$y] = [];
            $dSpecialYX[$y] = [];
            for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
                $dPieceYX[$y][$x] = $dPiece[$x][$y] ?? 0;
                $dTransYX[$y][$x] = $this->dTransVal[$x][$y] ?? 0;
                $dSpecialYX[$y][$x] = $this->dSpecial[$x][$y] ?? 0;
            }
        }

        return [
            'dungeon' => $dungeonYX,
            'dPiece' => $dPieceYX,
            'dTransVal' => $dTransYX,
            'dSpecial' => $dSpecialYX,
            'viewX' => $this->viewX,
            'viewY' => $this->viewY,
            'stairUp' => [$this->stairUpX, $this->stairUpY],
            'stairDown' => [$this->stairDownX, $this->stairDownY],
            'attempts' => $this->genAttempts,
            'area' => $this->findArea(),
        ];
    }

    private int $genAttempts = 0;

    private function generateLevel(Random $rng, int $dlvl, int $entry): void
    {
        $minarea = match ($dlvl) {
            1 => 533,
            2 => 693,
            default => 761,
        };

        $this->genAttempts = 0;
        while (true) {
            $this->genAttempts++;
            $this->drlgInitTrans();
            do {
                $this->firstRoom($rng);
            } while ($this->findArea() < $minarea);

            $this->initDungeonFlags();
            $this->makeDmt();
            $this->fillChambers();
            $this->fixTilesPatterns();
            $this->addWall($rng);
            $this->floodTransparencyValues(self::FLOOR);
            if ($this->placeCathedralStairs($rng, $entry)) {
                break;
            }
            if ($this->genAttempts > 400) {
                throw new \RuntimeException('PlaceCathedralStairs failed after 400 GenerateLevel attempts');
            }
        }

        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                if ($this->dungeonXY[$i][$j] === self::ENTRANCE_STAIRS) {
                    $xx = (2 * $i) + 16;
                    $yy = (2 * $j) + 16;
                    $this->drlgCopyTrans($xx, $yy + 1, $xx, $yy);
                    $this->drlgCopyTrans($xx + 1, $yy + 1, $xx + 1, $yy);
                }
            }
        }

        $this->fixTransparency();
        $this->fixDirtTiles();
        $this->fixCornerTiles();
        $this->substitution($rng);
        $this->applyShadowsPatterns();
        // Denser torch décor (DX places several lamp minisets per cathedral)
        $numt = $rng->generateRnd(7) + 8;
        for ($i = 0; $i < $numt; $i++) {
            $this->placeMiniSet($rng, self::LAMPS, DungeonMap::DMAXX * DungeonMap::DMAXY, true);
        }
        $this->fillFloor($rng);
    }

    private function firstRoom(Random $rng): void
    {
        $this->mask = [];
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            $this->mask[$y] = array_fill(0, DungeonMap::DMAXX, false);
        }

        $this->verticalLayout = $rng->flipCoin();
        $this->hasChamber1 = !$rng->flipCoin();
        $this->hasChamber2 = !$rng->flipCoin();
        $this->hasChamber3 = !$rng->flipCoin();
        if (!$this->hasChamber1 || !$this->hasChamber3) {
            $this->hasChamber2 = true;
        }

        $c1 = ['x' => 1, 'y' => 15, 'w' => 10, 'h' => 10];
        $c2 = ['x' => 15, 'y' => 15, 'w' => 10, 'h' => 10];
        $c3 = ['x' => 29, 'y' => 15, 'w' => 10, 'h' => 10];
        $hw = ['x' => 1, 'y' => 17, 'w' => 38, 'h' => 6];
        if (!$this->hasChamber1) {
            $hw['x'] += 17;
            $hw['w'] -= 17;
        }
        if (!$this->hasChamber3) {
            $hw['w'] -= 16;
        }
        if ($this->verticalLayout) {
            $c1 = ['x' => $c1['y'], 'y' => $c1['x'], 'w' => $c1['h'], 'h' => $c1['w']];
            $c3 = ['x' => $c3['y'], 'y' => $c3['x'], 'w' => $c3['h'], 'h' => $c3['w']];
            $hw = ['x' => $hw['y'], 'y' => $hw['x'], 'w' => $hw['h'], 'h' => $hw['w']];
        }
        if ($this->hasChamber1) {
            $this->mapRoom($c1);
        }
        if ($this->hasChamber2) {
            $this->mapRoom($c2);
        }
        if ($this->hasChamber3) {
            $this->mapRoom($c3);
        }
        $this->mapRoom($hw);
        if ($this->hasChamber1) {
            $this->generateRoom($rng, $c1, $this->verticalLayout);
        }
        if ($this->hasChamber2) {
            $this->generateRoom($rng, $c2, $this->verticalLayout);
        }
        if ($this->hasChamber3) {
            $this->generateRoom($rng, $c3, $this->verticalLayout);
        }
    }

    /** @param array{x:int,y:int,w:int,h:int} $room */
    private function mapRoom(array $room): void
    {
        for ($j = 0; $j < $room['h']; $j++) {
            for ($i = 0; $i < $room['w']; $i++) {
                $x = $room['x'] + $i;
                $y = $room['y'] + $j;
                if ($x >= 0 && $y >= 0 && $x < DungeonMap::DMAXX && $y < DungeonMap::DMAXY) {
                    $this->mask[$y][$x] = true;
                }
            }
        }
    }

    /** @param array{x:int,y:int,w:int,h:int} $room */
    private function checkRoom(array $room): bool
    {
        for ($j = 0; $j < $room['h']; $j++) {
            for ($i = 0; $i < $room['w']; $i++) {
                $x = $room['x'] + $i;
                $y = $room['y'] + $j;
                if ($x < 0 || $x >= DungeonMap::DMAXX || $y < 0 || $y >= DungeonMap::DMAXY) {
                    return false;
                }
                if ($this->mask[$y][$x]) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @param array{x:int,y:int,w:int,h:int} $area */
    private function generateRoom(Random $rng, array $area, bool $verticalLayout): void
    {
        $rotate = $rng->flipCoin(4);
        $verticalLayout = (!$verticalLayout && $rotate) || ($verticalLayout && !$rotate);
        $placeRoom1 = false;
        $room1 = ['x' => 0, 'y' => 0, 'w' => 2, 'h' => 2];
        for ($num = 0; $num < 20; $num++) {
            $rw = ($rng->generateRnd(5) + 2) & ~1;
            $rh = ($rng->generateRnd(5) + 2) & ~1;
            $room1 = ['x' => $area['x'], 'y' => $area['y'], 'w' => $rw, 'h' => $rh];
            if ($verticalLayout) {
                $room1['x'] -= $room1['w'];
                $room1['y'] += intdiv($area['h'], 2) - intdiv($room1['h'], 2);
                $placeRoom1 = $this->checkRoom([
                    'x' => $room1['x'] - 1,
                    'y' => $room1['y'] - 1,
                    'w' => $room1['h'] + 2,
                    'h' => $room1['w'] + 1,
                ]);
            } else {
                $room1['x'] += intdiv($area['w'], 2) - intdiv($room1['w'], 2);
                $room1['y'] -= $room1['h'];
                $placeRoom1 = $this->checkRoom([
                    'x' => $room1['x'] - 1,
                    'y' => $room1['y'] - 1,
                    'w' => $room1['w'] + 2,
                    'h' => $room1['h'] + 1,
                ]);
            }
            if ($placeRoom1) {
                break;
            }
        }
        if ($placeRoom1) {
            $this->mapRoom([
                'x' => $room1['x'],
                'y' => $room1['y'],
                'w' => min(DungeonMap::DMAXX - $room1['x'], $room1['w']),
                'h' => min(DungeonMap::DMAXY - $room1['y'], $room1['h']),
            ]);
        }
        $room2 = $room1;
        $placeRoom2 = false;
        if ($verticalLayout) {
            $room2['x'] = $area['x'] + $area['w'];
            $placeRoom2 = $this->checkRoom([
                'x' => $room2['x'],
                'y' => $room2['y'] - 1,
                'w' => $room2['w'] + 1,
                'h' => $room2['h'] + 2,
            ]);
        } else {
            $room2['y'] = $area['y'] + $area['h'];
            $placeRoom2 = $this->checkRoom([
                'x' => $room2['x'] - 1,
                'y' => $room2['y'],
                'w' => $room2['w'] + 2,
                'h' => $room2['h'] + 1,
            ]);
        }
        if ($placeRoom2) {
            $this->mapRoom($room2);
        }
        if ($placeRoom1) {
            $this->generateRoom($rng, $room1, !$verticalLayout);
        }
        if ($placeRoom2) {
            $this->generateRoom($rng, $room2, !$verticalLayout);
        }
    }

    private function findArea(): int
    {
        $n = 0;
        foreach ($this->mask as $row) {
            foreach ($row as $v) {
                if ($v) {
                    $n++;
                }
            }
        }

        return $n;
    }

    private function maskAt(int $x, int $y): bool
    {
        return $x >= 0 && $y >= 0 && $x < DungeonMap::DMAXX && $y < DungeonMap::DMAXY && ($this->mask[$y][$x] ?? false);
    }

    private function initDungeonFlags(): void
    {
        $this->dungeonXY = [];
        $this->protected = [];
        $this->chamber = [];
        for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
            $this->dungeonXY[$x] = array_fill(0, DungeonMap::DMAXY, self::DIRT);
            $this->protected[$x] = array_fill(0, DungeonMap::DMAXY, false);
            $this->chamber[$x] = array_fill(0, DungeonMap::DMAXY, false);
        }
    }

    private function makeDmt(): void
    {
        for ($j = 0; $j < DungeonMap::DMAXY - 1; $j++) {
            for ($i = 0; $i < DungeonMap::DMAXX - 1; $i++) {
                if ($this->maskAt($i, $j)) {
                    $this->dungeonXY[$i][$j] = self::FLOOR;
                } elseif (!$this->maskAt($i + 1, $j + 1) && $this->maskAt($i, $j + 1) && $this->maskAt($i + 1, $j)) {
                    $this->dungeonXY[$i][$j] = self::FLOOR;
                } elseif ($this->maskAt($i + 1, $j + 1) && $this->maskAt($i, $j + 1) && $this->maskAt($i + 1, $j)) {
                    $this->dungeonXY[$i][$j] = self::VCORNER;
                } elseif ($this->maskAt($i, $j + 1)) {
                    $this->dungeonXY[$i][$j] = self::HWALL;
                } elseif ($this->maskAt($i + 1, $j)) {
                    $this->dungeonXY[$i][$j] = self::VWALL;
                } elseif ($this->maskAt($i + 1, $j + 1)) {
                    $this->dungeonXY[$i][$j] = self::DWALL;
                } else {
                    $this->dungeonXY[$i][$j] = self::DIRT;
                }
            }
        }
    }

    private function fillChambers(): void
    {
        $chamber1 = ['x' => 0, 'y' => 14];
        $chamber3 = ['x' => 28, 'y' => 14];
        $hall1 = ['x' => 12, 'y' => 18];
        $hall2 = ['x' => 26, 'y' => 18];
        if ($this->verticalLayout) {
            $chamber1 = ['x' => $chamber1['y'], 'y' => $chamber1['x']];
            $chamber3 = ['x' => $chamber3['y'], 'y' => $chamber3['x']];
            $hall1 = ['x' => $hall1['y'], 'y' => $hall1['x']];
            $hall2 = ['x' => $hall2['y'], 'y' => $hall2['x']];
        }
        if ($this->hasChamber1) {
            $this->generateChamber($chamber1['x'], $chamber1['y'], false, true, $this->verticalLayout);
        }
        if ($this->hasChamber2) {
            $this->generateChamber(14, 14, $this->hasChamber1, $this->hasChamber3, $this->verticalLayout);
        }
        if ($this->hasChamber3) {
            $this->generateChamber($chamber3['x'], $chamber3['y'], true, false, $this->verticalLayout);
        }
        if ($this->hasChamber2) {
            if ($this->hasChamber1) {
                $this->generateHall($hall1['x'], $hall1['y'], 2, $this->verticalLayout);
            }
            if ($this->hasChamber3) {
                $this->generateHall($hall2['x'], $hall2['y'], 2, $this->verticalLayout);
            }
        } else {
            $this->generateHall($hall1['x'], $hall1['y'], 16, $this->verticalLayout);
        }
        // InitSetPiece deferred (quests)
    }

    private function generateChamber(int $px, int $py, bool $connectPrevious, bool $connectNext, bool $verticalLayout): void
    {
        if ($connectPrevious) {
            if ($verticalLayout) {
                $this->dungeonXY[$px + 2][$py] = self::HARCH;
                $this->dungeonXY[$px + 3][$py] = self::HARCH;
                $this->dungeonXY[$px + 4][$py] = self::CORNER;
                $this->dungeonXY[$px + 7][$py] = self::VARCH_END;
                $this->dungeonXY[$px + 8][$py] = self::HARCH;
                $this->dungeonXY[$px + 9][$py] = self::HWALL;
            } else {
                $this->dungeonXY[$px][$py + 2] = self::VARCH;
                $this->dungeonXY[$px][$py + 3] = self::VARCH;
                $this->dungeonXY[$px][$py + 4] = self::CORNER;
                $this->dungeonXY[$px][$py + 7] = self::HARCH_END;
                $this->dungeonXY[$px][$py + 8] = self::VARCH;
                $this->dungeonXY[$px][$py + 9] = self::VWALL;
            }
        }
        if ($connectNext) {
            if ($verticalLayout) {
                $y = $py + 11;
                $this->dungeonXY[$px + 2][$y] = self::HARCH_VWALL;
                $this->dungeonXY[$px + 3][$y] = self::HARCH;
                $this->dungeonXY[$px + 4][$y] = self::HARCH_END;
                $this->dungeonXY[$px + 7][$y] = self::DARCH;
                $this->dungeonXY[$px + 8][$y] = self::HARCH;
                if ($this->dungeonXY[$px + 9][$y] !== self::DWALL) {
                    $this->dungeonXY[$px + 9][$y] = self::HDIRT_CORNER;
                }
            } else {
                $x = $px + 11;
                $this->dungeonXY[$x][$py + 2] = self::HWALL_VARCH;
                $this->dungeonXY[$x][$py + 3] = self::VARCH;
                $this->dungeonXY[$x][$py + 4] = self::VARCH_END;
                $this->dungeonXY[$x][$py + 7] = self::DARCH;
                $this->dungeonXY[$x][$py + 8] = self::VARCH;
                if ($this->dungeonXY[$x][$py + 9] !== self::DWALL) {
                    $this->dungeonXY[$x][$py + 9] = self::HDIRT_CORNER;
                }
            }
        }
        for ($y = 1; $y < 11; $y++) {
            for ($x = 1; $x < 11; $x++) {
                $this->dungeonXY[$px + $x][$py + $y] = self::FLOOR;
                $this->chamber[$px + $x][$py + $y] = true;
            }
        }
        $this->dungeonXY[$px + 4][$py + 4] = self::PILLAR;
        $this->dungeonXY[$px + 7][$py + 4] = self::PILLAR;
        $this->dungeonXY[$px + 4][$py + 7] = self::PILLAR;
        $this->dungeonXY[$px + 7][$py + 7] = self::PILLAR;
    }

    private function generateHall(int $sx, int $sy, int $length, bool $verticalLayout): void
    {
        if ($verticalLayout) {
            for ($i = $sy; $i < $sy + $length; $i++) {
                $this->dungeonXY[$sx][$i] = self::VARCH;
                $this->dungeonXY[$sx + 3][$i] = self::VARCH;
            }
        } else {
            for ($i = $sx; $i < $sx + $length; $i++) {
                $this->dungeonXY[$i][$sy] = self::HARCH;
                $this->dungeonXY[$i][$sy + 3] = self::HARCH;
            }
        }
    }

    private function isAnyOf(int $tile, int ...$ids): bool
    {
        return in_array($tile, $ids, true);
    }

    private function fixTilesPatterns(): void
    {
        $d = &$this->dungeonXY;
        $DX = DungeonMap::DMAXX;
        $DY = DungeonMap::DMAXY;
        for ($j = 0; $j < $DY; $j++) {
            for ($i = 0; $i < $DX; $i++) {
                if ($i + 1 < $DX) {
                    if ($d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::DIRT_HWALL_END;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::DIRT_HWALL;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i + 1][$j] === self::HWALL) {
                        $d[$i + 1][$j] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::DIRT_VWALL_END;
                    }
                }
                if ($j + 1 < $DY) {
                    if ($d[$i][$j] === self::VWALL && $d[$i][$j + 1] === self::DIRT) {
                        $d[$i][$j + 1] = self::DIRT_VWALL_END;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i][$j + 1] === self::VWALL) {
                        $d[$i][$j + 1] = self::VWALL_END;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i][$j + 1] === self::DIRT) {
                        $d[$i][$j + 1] = self::DIRT_VWALL;
                    }
                }
            }
        }
        for ($j = 0; $j < $DY; $j++) {
            for ($i = 0; $i < $DX; $i++) {
                if ($i + 1 < $DX) {
                    if ($d[$i][$j] === self::FLOOR && $d[$i + 1][$j] === self::DIRT_VWALL) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::VDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::DIRT_HWALL_END;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i + 1][$j] === self::DIRT_VWALL_END) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::DIRT_VWALL && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::VDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::DIRT_VWALL) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::DIRT_VWALL && $d[$i + 1][$j] === self::VWALL) {
                        $d[$i + 1][$j] = self::VWALL_END;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i + 1][$j] === self::DIRT_VWALL) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::VWALL) {
                        $d[$i + 1][$j] = self::VWALL_END;
                    }
                    if ($d[$i][$j] === self::CORNER && $d[$i + 1][$j] === self::DIRT) {
                        $d[$i + 1][$j] = self::DIRT_VWALL_END;
                    }
                    if ($d[$i][$j] === self::HDIRT_CORNER && $d[$i + 1][$j] === self::VWALL) {
                        $d[$i + 1][$j] = self::VWALL_END;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i + 1][$j] === self::VWALL) {
                        $d[$i + 1][$j] = self::VWALL_END;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i + 1][$j] === self::DIRT_VWALL_END) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::DWALL && $d[$i + 1][$j] === self::VCORNER) {
                        $d[$i + 1][$j] = self::HCORNER;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i + 1][$j] === self::FLOOR) {
                        $d[$i + 1][$j] = self::HCORNER;
                    }
                    if ($d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::DIRT_VWALL_END) {
                        $d[$i + 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::FLOOR) {
                        $d[$i + 1][$j] = self::HCORNER;
                    }
                }
                if ($i > 0) {
                    if ($d[$i][$j] === self::DIRT_HWALL_END && $d[$i - 1][$j] === self::DIRT) {
                        $d[$i - 1][$j] = self::DIRT_VWALL;
                    }
                    if ($d[$i][$j] === self::DIRT_VWALL && $d[$i - 1][$j] === self::DIRT_HWALL_END) {
                        $d[$i - 1][$j] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i - 1][$j] === self::DIRT) {
                        $d[$i - 1][$j] = self::DIRT_VWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i - 1][$j] === self::DIRT_HWALL_END) {
                        $d[$i - 1][$j] = self::HDIRT_CORNER;
                    }
                }
                if ($j + 1 < $DY) {
                    if ($d[$i][$j] === self::VWALL && $d[$i][$j + 1] === self::HWALL) {
                        $d[$i][$j + 1] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j + 1] === self::DIRT_HWALL) {
                        $d[$i][$j + 1] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::DIRT_HWALL && $d[$i][$j + 1] === self::HWALL) {
                        $d[$i][$j + 1] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j + 1] === self::HWALL) {
                        $d[$i][$j + 1] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::HDIRT_CORNER && $d[$i][$j + 1] === self::HWALL) {
                        $d[$i][$j + 1] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j + 1] === self::DIRT) {
                        $d[$i][$j + 1] = self::DIRT_VWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j + 1] === self::FLOOR) {
                        $d[$i][$j + 1] = self::VCORNER;
                    }
                    if ($d[$i][$j] === self::VWALL && $d[$i][$j + 1] === self::FLOOR) {
                        $d[$i][$j + 1] = self::VCORNER;
                    }
                    if ($d[$i][$j] === self::FLOOR && $d[$i][$j + 1] === self::VCORNER) {
                        $d[$i][$j + 1] = self::HCORNER;
                    }
                }
                if ($j > 0) {
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j - 1] === self::DIRT) {
                        $d[$i][$j - 1] = self::HWALL_END;
                    }
                    if ($d[$i][$j] === self::VWALL_END && $d[$i][$j - 1] === self::DIRT) {
                        $d[$i][$j - 1] = self::DIRT_VWALL_END;
                    }
                    if ($d[$i][$j] === self::HWALL_END && $d[$i][$j - 1] === self::DIRT_VWALL_END) {
                        $d[$i][$j - 1] = self::HDIRT_CORNER;
                    }
                    if ($d[$i][$j] === self::DIRT_HWALL && $d[$i][$j - 1] === self::DIRT_VWALL_END) {
                        $d[$i][$j - 1] = self::HDIRT_CORNER;
                    }
                }
            }
        }
        for ($j = 0; $j < $DY; $j++) {
            for ($i = 0; $i < $DX; $i++) {
                if ($j + 1 < $DY && $d[$i][$j] === self::DWALL && $d[$i][$j + 1] === self::HWALL) {
                    $d[$i][$j + 1] = self::HWALL_END;
                }
                if ($i + 1 < $DX && $d[$i][$j] === self::HWALL && $d[$i + 1][$j] === self::DIRT_VWALL) {
                    $d[$i + 1][$j] = self::HDIRT_CORNER;
                }
                if ($j + 1 < $DY && $d[$i][$j] === self::DIRT_HWALL && $d[$i][$j + 1] === self::DIRT) {
                    $d[$i][$j + 1] = self::VDIRT_CORNER;
                }
            }
        }
    }

    private function wallOkEnds(int $tileId): bool
    {
        return $this->isAnyOf(
            $tileId,
            self::CORNER,
            self::DWALL,
            self::DARCH,
            self::VWALL_END,
            self::HWALL_END,
            self::VCORNER,
            self::HCORNER,
            self::DIRT_HWALL,
            self::DIRT_VWALL,
            self::VDIRT_CORNER,
            self::HDIRT_CORNER,
            self::DIRT_HWALL_END,
            self::DIRT_VWALL_END
        );
    }

    private function horizontalWallOk(int $x, int $y): int
    {
        $length = 1;
        for (; ($this->dungeonXY[$x + $length][$y] ?? -1) === self::FLOOR; $length++) {
            if (
                ($this->dungeonXY[$x + $length][$y - 1] ?? -1) !== self::FLOOR
                || ($this->dungeonXY[$x + $length][$y + 1] ?? -1) !== self::FLOOR
                || ($this->protected[$x + $length][$y] ?? false)
                || ($this->chamber[$x + $length][$y] ?? false)
            ) {
                break;
            }
        }
        if ($length === 1) {
            return -1;
        }
        $tileId = $this->dungeonXY[$x + $length][$y] ?? -1;
        if (!$this->wallOkEnds($tileId)) {
            return -1;
        }

        return $length;
    }

    private function verticalWallOk(int $x, int $y): int
    {
        $length = 1;
        for (; ($this->dungeonXY[$x][$y + $length] ?? -1) === self::FLOOR; $length++) {
            if (
                ($this->dungeonXY[$x - 1][$y + $length] ?? -1) !== self::FLOOR
                || ($this->dungeonXY[$x + 1][$y + $length] ?? -1) !== self::FLOOR
                || ($this->protected[$x][$y + $length] ?? false)
                || ($this->chamber[$x][$y + $length] ?? false)
            ) {
                break;
            }
        }
        if ($length === 1) {
            return -1;
        }
        $tileId = $this->dungeonXY[$x][$y + $length] ?? -1;
        if (!$this->wallOkEnds($tileId)) {
            return -1;
        }

        return $length;
    }

    private function horizontalWall(Random $rng, int $x, int $y, int $start, int $maxX): void
    {
        $wallTile = self::HWALL;
        $doorTile = self::HDOOR;
        switch ($rng->generateRnd(4)) {
            case 2:
                $wallTile = self::HARCH;
                $doorTile = self::HARCH;
                if ($start === self::HWALL) {
                    $start = self::HARCH;
                } elseif ($start === self::DWALL) {
                    $start = self::HARCH_VWALL;
                }
                break;
            case 3:
                $wallTile = self::HFENCE;
                if ($start === self::HWALL) {
                    $start = self::HFENCE;
                } elseif ($start === self::DWALL) {
                    $start = self::HFENCE_VWALL;
                }
                break;
        }
        if ($rng->generateRnd(6) === 5) {
            $doorTile = self::HARCH;
        }
        $this->dungeonXY[$x][$y] = $start;
        for ($i = 1; $i < $maxX; $i++) {
            $this->dungeonXY[$x + $i][$y] = $wallTile;
        }
        $dx = $rng->generateRnd($maxX - 1) + 1;
        $this->dungeonXY[$x + $dx][$y] = $doorTile;
        if ($doorTile === self::HDOOR) {
            $this->protected[$x + $dx][$y] = true;
        }
    }

    private function verticalWall(Random $rng, int $x, int $y, int $start, int $maxY): void
    {
        $wallTile = self::VWALL;
        $doorTile = self::VDOOR;
        switch ($rng->generateRnd(4)) {
            case 2:
                $wallTile = self::VARCH;
                $doorTile = self::VARCH;
                if ($start === self::VWALL) {
                    $start = self::VARCH;
                } elseif ($start === self::DWALL) {
                    $start = self::HWALL_VARCH;
                }
                break;
            case 3:
                $wallTile = self::VFENCE;
                if ($start === self::VWALL) {
                    $start = self::VFENCE;
                } elseif ($start === self::DWALL) {
                    $start = self::HWALL_VFENCE;
                }
                break;
        }
        if ($rng->generateRnd(6) === 5) {
            $doorTile = self::VARCH;
        }
        $this->dungeonXY[$x][$y] = $start;
        for ($i = 1; $i < $maxY; $i++) {
            $this->dungeonXY[$x][$y + $i] = $wallTile;
        }
        $dy = $rng->generateRnd($maxY - 1) + 1;
        $this->dungeonXY[$x][$y + $dy] = $doorTile;
        if ($doorTile === self::VDOOR) {
            $this->protected[$x][$y + $dy] = true;
        }
    }

    private function addWall(Random $rng): void
    {
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                if (($this->protected[$i][$j] ?? false) || ($this->chamber[$i][$j] ?? false)) {
                    continue;
                }
                if ($this->dungeonXY[$i][$j] === self::CORNER) {
                    $rng->discardRandomValues(1);
                    $maxX = $this->horizontalWallOk($i, $j);
                    if ($maxX !== -1) {
                        $this->horizontalWall($rng, $i, $j, self::HWALL, $maxX);
                    }
                }
                if ($this->dungeonXY[$i][$j] === self::CORNER) {
                    $rng->discardRandomValues(1);
                    $maxY = $this->verticalWallOk($i, $j);
                    if ($maxY !== -1) {
                        $this->verticalWall($rng, $i, $j, self::VWALL, $maxY);
                    }
                }
                if ($this->dungeonXY[$i][$j] === self::VWALL_END) {
                    $rng->discardRandomValues(1);
                    $maxX = $this->horizontalWallOk($i, $j);
                    if ($maxX !== -1) {
                        $this->horizontalWall($rng, $i, $j, self::DWALL, $maxX);
                    }
                }
                if ($this->dungeonXY[$i][$j] === self::HWALL_END) {
                    $rng->discardRandomValues(1);
                    $maxY = $this->verticalWallOk($i, $j);
                    if ($maxY !== -1) {
                        $this->verticalWall($rng, $i, $j, self::DWALL, $maxY);
                    }
                }
                if ($this->dungeonXY[$i][$j] === self::HWALL) {
                    $rng->discardRandomValues(1);
                    $maxX = $this->horizontalWallOk($i, $j);
                    if ($maxX !== -1) {
                        $this->horizontalWall($rng, $i, $j, self::HWALL, $maxX);
                    }
                }
                if ($this->dungeonXY[$i][$j] === self::VWALL) {
                    $rng->discardRandomValues(1);
                    $maxY = $this->verticalWallOk($i, $j);
                    if ($maxY !== -1) {
                        $this->verticalWall($rng, $i, $j, self::VWALL, $maxY);
                    }
                }
            }
        }
    }

    private function drlgInitTrans(): void
    {
        $this->dTransVal = [];
        for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
            $this->dTransVal[$x] = array_fill(0, DungeonMap::MAXDUNY, 0);
        }
        $this->transVal = 1;
    }

    private function isFloorMega(int $wx, int $wy, int $floorID): bool
    {
        $i = intdiv($wx - 16, 2);
        $j = intdiv($wy - 16, 2);
        if ($i < 0 || $i >= DungeonMap::DMAXX || $j < 0 || $j >= DungeonMap::DMAXY) {
            return false;
        }

        return ($this->dungeonXY[$i][$j] ?? -1) === $floorID;
    }

    private function fillTransparencyValues(int $fx, int $fy, int $floorID): void
    {
        $dirs = [[0, -1], [0, 1], [1, 0], [-1, 0], [1, -1], [-1, -1], [1, 1], [-1, 1]];
        foreach ($dirs as [$dx, $dy]) {
            $ax = $fx + $dx;
            $ay = $fy + $dy;
            if ($ax < 0 || $ay < 0 || $ax >= DungeonMap::MAXDUNX || $ay >= DungeonMap::MAXDUNY) {
                continue;
            }
            if (!$this->isFloorMega($ax, $ay, $floorID)) {
                $this->dTransVal[$ax][$ay] = $this->transVal;
            }
        }
        $this->dTransVal[$fx][$fy] = $this->transVal;
    }

    /** Simplified BFS flood (span-fill parity deferred); marks all connected floor micros. */
    private function findTransparencyValues(int $fx, int $fy, int $floorID): void
    {
        $queue = [[$fx, $fy]];
        $seen = [];
        while ($queue !== []) {
            [$x, $y] = array_shift($queue);
            $key = $x . ',' . $y;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            if ($x < 0 || $y < 0 || $x >= DungeonMap::MAXDUNX || $y >= DungeonMap::MAXDUNY) {
                continue;
            }
            if (($this->dTransVal[$x][$y] ?? 0) !== 0) {
                continue;
            }
            if (!$this->isFloorMega($x, $y, $floorID)) {
                continue;
            }
            $this->fillTransparencyValues($x, $y, $floorID);
            foreach ([[0, -1], [0, 1], [1, 0], [-1, 0], [1, -1], [-1, -1], [1, 1], [-1, 1]] as [$dx, $dy]) {
                $queue[] = [$x + $dx, $y + $dy];
            }
        }
    }

    private function floodTransparencyValues(int $floorID): void
    {
        $yy = 16;
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            $xx = 16;
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                if (($this->dungeonXY[$i][$j] ?? -1) === $floorID && ($this->dTransVal[$xx][$yy] ?? 0) === 0) {
                    $this->findTransparencyValues($xx, $yy, $floorID);
                    $this->transVal++;
                }
                $xx += 2;
            }
            $yy += 2;
        }
    }

    private function drlgCopyTrans(int $sx, int $sy, int $dx, int $dy): void
    {
        $this->dTransVal[$dx][$dy] = $this->dTransVal[$sx][$sy] ?? 0;
    }

    /** @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset */
    private function minisetMatches(array $miniset, int $px, int $py, bool $respectProtected = true): bool
    {
        for ($yy = 0; $yy < $miniset['h']; $yy++) {
            for ($xx = 0; $xx < $miniset['w']; $xx++) {
                $s = $miniset['search'][$yy][$xx];
                if ($s !== 0 && ($this->dungeonXY[$xx + $px][$yy + $py] ?? -1) !== $s) {
                    return false;
                }
                if ($respectProtected && ($this->protected[$xx + $px][$yy + $py] ?? false)) {
                    return false;
                }
            }
        }

        return true;
    }

    /** @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset */
    private function minisetPlace(array $miniset, int $px, int $py, bool $protect = false): void
    {
        for ($y = 0; $y < $miniset['h']; $y++) {
            for ($x = 0; $x < $miniset['w']; $x++) {
                $r = $miniset['replace'][$y][$x];
                if ($r === 0) {
                    continue;
                }
                $this->dungeonXY[$x + $px][$y + $py] = $r;
                if ($protect) {
                    $this->protected[$x + $px][$y + $py] = true;
                }
            }
        }
    }

    /**
     * @param array{w:int,h:int,search:list<list<int>>,replace:list<list<int>>} $miniset
     * @return array{0:int,1:int}|null
     */
    private function placeMiniSet(Random $rng, array $miniset, int $tries, bool $drlg1Quirk): ?array
    {
        $sw = $miniset['w'];
        $sh = $miniset['h'];
        $px = $rng->generateRnd(DungeonMap::DMAXX - $sw);
        $py = $rng->generateRnd(DungeonMap::DMAXY - $sh);
        for ($i = 0; $i < $tries; $i++, $px++) {
            if ($px === DungeonMap::DMAXX - $sw) {
                $px = 0;
                $py++;
                if ($py === DungeonMap::DMAXY - $sh) {
                    $py = 0;
                }
            }
            if ($drlg1Quirk) {
                $valid = true;
                if ($px <= 12) {
                    $px++;
                    $valid = false;
                }
                if ($py <= 12) {
                    $py++;
                    $valid = false;
                }
                if (!$valid) {
                    continue;
                }
            }
            if (!$this->minisetMatches($miniset, $px, $py, true)) {
                continue;
            }
            $this->minisetPlace($miniset, $px, $py, false);

            return [$px, $py];
        }

        return null;
    }

    private function placeCathedralStairs(Random $rng, int $entry): bool
    {
        $success = true;
        $position = $this->placeMiniSet($rng, self::STAIRSUP, DungeonMap::DMAXX * DungeonMap::DMAXY, true);
        if ($position === null) {
            $success = false;
        } else {
            $this->stairUpX = $position[0];
            $this->stairUpY = $position[1];
            if ($entry === 0) { // ENTRY_MAIN
                $this->viewX = 16 + 2 * $position[0] + 3;
                $this->viewY = 16 + 2 * $position[1] + 4;
            }
        }
        $position = $this->placeMiniSet($rng, self::STAIRSDOWN, DungeonMap::DMAXX * DungeonMap::DMAXY, true);
        if ($position === null) {
            $success = false;
        } else {
            $this->stairDownX = $position[0];
            $this->stairDownY = $position[1];
            if ($entry === 1) { // ENTRY_PREV
                $this->viewX = 16 + 2 * $position[0] + 3;
                $this->viewY = 16 + 2 * $position[1] + 3;
            }
        }
        if ($this->viewX === 0 && $this->viewY === 0 && $success) {
            $this->viewX = 16 + 2 * $this->stairUpX + 3;
            $this->viewY = 16 + 2 * $this->stairUpY + 4;
        }

        return $success;
    }

    private function fixTransparency(): void
    {
        $yy = 16;
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            $xx = 16;
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                $t = $this->dungeonXY[$i][$j] ?? 0;
                if ($t === self::DIRT_HWALL_END && $j > 0 && ($this->dungeonXY[$i][$j - 1] ?? 0) === self::DIRT_HWALL) {
                    $this->dTransVal[$xx + 1][$yy] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx + 1][$yy + 1] = $this->dTransVal[$xx][$yy];
                }
                if ($t === self::DIRT_VWALL_END && $i + 1 < DungeonMap::DMAXX && ($this->dungeonXY[$i + 1][$j] ?? 0) === self::DIRT_VWALL) {
                    $this->dTransVal[$xx][$yy + 1] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx + 1][$yy + 1] = $this->dTransVal[$xx][$yy];
                }
                if ($t === self::DIRT_HWALL) {
                    $this->dTransVal[$xx + 1][$yy] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx + 1][$yy + 1] = $this->dTransVal[$xx][$yy];
                }
                if ($t === self::DIRT_VWALL) {
                    $this->dTransVal[$xx][$yy + 1] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx + 1][$yy + 1] = $this->dTransVal[$xx][$yy];
                }
                if ($t === self::VDIRT_CORNER) {
                    $this->dTransVal[$xx + 1][$yy] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx][$yy + 1] = $this->dTransVal[$xx][$yy];
                    $this->dTransVal[$xx + 1][$yy + 1] = $this->dTransVal[$xx][$yy];
                }
                $xx += 2;
            }
            $yy += 2;
        }
    }

    private function fixDirtTiles(): void
    {
        for ($j = 0; $j < DungeonMap::DMAXY - 1; $j++) {
            for ($i = 0; $i < DungeonMap::DMAXX - 1; $i++) {
                if ($this->dungeonXY[$i][$j] === self::HDIRT_CORNER && $this->dungeonXY[$i + 1][$j] !== self::DIRT_VWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_CORNER2;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_VWALL && $this->dungeonXY[$i + 1][$j] !== self::DIRT_VWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_VWALL2;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_VWALL_END && $this->dungeonXY[$i + 1][$j] !== self::DIRT_VWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_VWALL_END2;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_HWALL && $this->dungeonXY[$i][$j + 1] !== self::DIRT_HWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_HWALL2;
                }
                if ($this->dungeonXY[$i][$j] === self::HDIRT_CORNER && $this->dungeonXY[$i][$j + 1] !== self::DIRT_HWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_CORNER2;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_HWALL_END && $this->dungeonXY[$i][$j + 1] !== self::DIRT_HWALL) {
                    $this->dungeonXY[$i][$j] = self::DIRT_HWALL_END2;
                }
            }
        }
    }

    private function fixCornerTiles(): void
    {
        for ($j = 1; $j < DungeonMap::DMAXY - 1; $j++) {
            for ($i = 1; $i < DungeonMap::DMAXX - 1; $i++) {
                if (
                    !($this->protected[$i][$j] ?? false)
                    && $this->dungeonXY[$i][$j] === self::HCORNER
                    && $this->dungeonXY[$i - 1][$j] === self::FLOOR
                    && $this->dungeonXY[$i][$j - 1] === self::VWALL
                ) {
                    $this->dungeonXY[$i][$j] = self::VCORNER;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_CORNER2 && $this->dungeonXY[$i + 1][$j] === self::FLOOR && $this->dungeonXY[$i][$j + 1] === self::VWALL) {
                    $this->dungeonXY[$i][$j] = self::HARCH_END;
                }
                if ($this->dungeonXY[$i][$j] === self::DIRT_CORNER2 && $this->dungeonXY[$i][$j + 1] === self::FLOOR && $this->dungeonXY[$i + 1][$j] === self::HWALL) {
                    $this->dungeonXY[$i][$j] = self::VARCH_END;
                }
            }
        }
    }

    private function substitution(Random $rng): void
    {
        $decor = DrlgL1Tables::tileDecorations();
        $decorCount = count($decor);
        for ($y = 0; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 0; $x < DungeonMap::DMAXX; $x++) {
                if (!$rng->flipCoin(4)) {
                    continue;
                }
                $tile = $this->dungeonXY[$x][$y] ?? 0;
                $c = $decor[$tile] ?? 0;
                if ($c === 0 || ($this->protected[$x][$y] ?? false)) {
                    continue;
                }
                $rv = $rng->generateRnd(16);
                $i = -1;
                while ($rv >= 0) {
                    $i++;
                    if ($i === $decorCount) {
                        $i = 0;
                    }
                    if ($c === ($decor[$i] ?? -1)) {
                        $rv--;
                    }
                }
                if ($i === self::VWALL4 && $y > 0) {
                    if (($decor[$this->dungeonXY[$x][$y - 1] ?? 0] ?? 0) !== self::VWALL2 || ($this->protected[$x][$y - 1] ?? false)) {
                        $i = self::VWALL2;
                    } else {
                        $this->dungeonXY[$x][$y - 1] = self::VWALL5;
                    }
                }
                if ($i === self::HWALL4 && $x + 1 < DungeonMap::DMAXX) {
                    if (($decor[$this->dungeonXY[$x + 1][$y] ?? 0] ?? 0) !== self::HWALL2 || ($this->protected[$x + 1][$y] ?? false)) {
                        $i = self::HWALL2;
                    } else {
                        $this->dungeonXY[$x + 1][$y] = self::HWALL5;
                    }
                }
                $this->dungeonXY[$x][$y] = $i;
            }
        }
    }

    private function applyShadowsPatterns(): void
    {
        $base = DrlgL1Tables::baseTypes();
        $patterns = DrlgL1Tables::shadowPatterns();
        for ($y = 1; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX; $x++) {
                $s00 = $base[$this->dungeonXY[$x][$y] ?? 0] ?? 0;
                $s10 = $base[$this->dungeonXY[$x - 1][$y] ?? 0] ?? 0;
                $s01 = $base[$this->dungeonXY[$x][$y - 1] ?? 0] ?? 0;
                $s11 = $base[$this->dungeonXY[$x - 1][$y - 1] ?? 0] ?? 0;
                foreach ($patterns as $shadow) {
                    [$strig, $s1, $s2, $s3, $nv1, $nv2, $nv3] = $shadow;
                    if ($strig !== $s00) {
                        continue;
                    }
                    if ($s1 !== 0 && $s1 !== $s11) {
                        continue;
                    }
                    if ($s2 !== 0 && $s2 !== $s01) {
                        continue;
                    }
                    if ($s3 !== 0 && $s3 !== $s10) {
                        continue;
                    }
                    if ($nv1 !== 0 && !($this->protected[$x - 1][$y - 1] ?? false)) {
                        $this->dungeonXY[$x - 1][$y - 1] = $nv1;
                    }
                    if ($nv2 !== 0 && !($this->protected[$x][$y - 1] ?? false)) {
                        $this->dungeonXY[$x][$y - 1] = $nv2;
                    }
                    if ($nv3 !== 0 && !($this->protected[$x - 1][$y] ?? false)) {
                        $this->dungeonXY[$x - 1][$y] = $nv3;
                    }
                }
            }
        }
        $fence = [self::DFENCE, self::VFENCE_END, self::VFENCE, self::HWALL_VFENCE, self::HARCH_VFENCE, self::HARCH_VDOOR];
        for ($y = 1; $y < DungeonMap::DMAXY; $y++) {
            for ($x = 1; $x < DungeonMap::DMAXX; $x++) {
                if ($this->protected[$x - 1][$y] ?? false) {
                    continue;
                }
                $cur = $this->dungeonXY[$x][$y] ?? 0;
                if ($this->dungeonXY[$x - 1][$y] === self::FLOOR12) {
                    $this->dungeonXY[$x - 1][$y] = in_array($cur, $fence, true) ? self::FLOOR14 : self::FLOOR12;
                }
                if ($this->dungeonXY[$x - 1][$y] === self::HARCH_SHADOW) {
                    $this->dungeonXY[$x - 1][$y] = in_array($cur, $fence, true) ? self::HARCH_SHADOW2 : self::HARCH_SHADOW;
                }
                if ($this->dungeonXY[$x - 1][$y] === self::HWALL_SHADOW) {
                    $this->dungeonXY[$x - 1][$y] = in_array($cur, $fence, true) ? self::HWALL_SHADOW2 : self::HWALL_SHADOW;
                }
            }
        }
    }

    private function fillFloor(Random $rng): void
    {
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                if ($this->dungeonXY[$i][$j] !== self::FLOOR || ($this->protected[$i][$j] ?? false)) {
                    continue;
                }
                $rv = $rng->randomIntLessThan(3);
                if ($rv === 1) {
                    $this->dungeonXY[$i][$j] = self::FLOOR22;
                } elseif ($rv === 2) {
                    $this->dungeonXY[$i][$j] = self::FLOOR23;
                }
            }
        }
    }

    /**
     * @param list<array{micro1:int,micro2:int,micro3:int,micro4:int}> $megaTiles
     * @return list<list<int>> dPiece[x][y]
     */
    private function drlgLPass3(array $megaTiles): array
    {
        $dirtId = self::DIRT - 1;
        $mega = $megaTiles[$dirtId] ?? ['micro1' => 0, 'micro2' => 0, 'micro3' => 0, 'micro4' => 0];
        $v1 = $mega['micro1'];
        $v2 = $mega['micro2'];
        $v3 = $mega['micro3'];
        $v4 = $mega['micro4'];
        $dPiece = [];
        for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
            $dPiece[$x] = array_fill(0, DungeonMap::MAXDUNY, 0);
        }
        for ($j = 0; $j < DungeonMap::MAXDUNY; $j += 2) {
            for ($i = 0; $i < DungeonMap::MAXDUNX; $i += 2) {
                $dPiece[$i][$j] = $v1;
                $dPiece[$i + 1][$j] = $v2;
                $dPiece[$i][$j + 1] = $v3;
                $dPiece[$i + 1][$j + 1] = $v4;
            }
        }
        $yy = 16;
        for ($j = 0; $j < DungeonMap::DMAXY; $j++) {
            $xx = 16;
            for ($i = 0; $i < DungeonMap::DMAXX; $i++) {
                $tileId = ($this->dungeonXY[$i][$j] ?? self::DIRT) - 1;
                if ($tileId < 0 || !isset($megaTiles[$tileId])) {
                    $tileId = $dirtId;
                }
                $m = $megaTiles[$tileId];
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

    /** @param list<list<int>> $dPiece */
    private function initDungeonPieces(array $dPiece): void
    {
        $this->dSpecial = [];
        for ($x = 0; $x < DungeonMap::MAXDUNX; $x++) {
            $this->dSpecial[$x] = array_fill(0, DungeonMap::MAXDUNY, 0);
        }
        $map = [
            11 => 1, 70 => 1, 320 => 1, 210 => 1, 340 => 1, 417 => 1,
            10 => 2, 248 => 2, 324 => 2, 343 => 2, 330 => 2, 420 => 2,
            252 => 3, 254 => 4, 258 => 5, 266 => 6,
        ];
        for ($j = 0; $j < DungeonMap::MAXDUNY; $j++) {
            for ($i = 0; $i < DungeonMap::MAXDUNX; $i++) {
                $pc = $map[$dPiece[$i][$j] ?? 0] ?? 0;
                if ($pc !== 0) {
                    $this->dSpecial[$i][$j] = $pc;
                }
            }
        }
    }
}
