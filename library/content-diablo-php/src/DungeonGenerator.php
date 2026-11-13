<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Seeded rooms+corridors dungeon. Deterministic via Rng LCG.
 */
final class DungeonGenerator
{
    public int $width = 40;
    public int $height = 40;

    /**
     * @return array{
     *   dungeon: Dungeon,
     *   monsters: list<array{x:int,y:int,type:string}>,
     *   objects: list<array{x:int,y:int,type:string}>,
     *   items: list<array{x:int,y:int,item:string}>
     * }
     */
    public function generate(Rng $rng, int $depth = 1): array
    {
        $w = $this->width;
        $h = $this->height;
        $tiles = [];
        for ($y = 0; $y < $h; $y++) {
            $row = [];
            for ($x = 0; $x < $w; $x++) {
                $row[] = Dungeon::TILE_WALL;
            }
            $tiles[] = $row;
        }

        $rooms = [];
        $roomCount = 6 + $rng->between(0, 4);
        for ($i = 0; $i < $roomCount; $i++) {
            $rw = $rng->between(4, 8);
            $rh = $rng->between(4, 8);
            $rx = $rng->between(1, $w - $rw - 2);
            $ry = $rng->between(1, $h - $rh - 2);
            $rooms[] = [$rx, $ry, $rw, $rh];
            for ($y = $ry; $y < $ry + $rh; $y++) {
                for ($x = $rx; $x < $rx + $rw; $x++) {
                    $tiles[$y][$x] = Dungeon::TILE_FLOOR;
                }
            }
        }

        // Connect room centers
        for ($i = 1; $i < count($rooms); $i++) {
            [$x1, $y1] = [$rooms[$i - 1][0] + intdiv($rooms[$i - 1][2], 2), $rooms[$i - 1][1] + intdiv($rooms[$i - 1][3], 2)];
            [$x2, $y2] = [$rooms[$i][0] + intdiv($rooms[$i][2], 2), $rooms[$i][1] + intdiv($rooms[$i][3], 2)];
            $this->carveCorridor($tiles, $x1, $y1, $x2, $y2, $rng);
        }

        // Doors at some corridor mouths
        $doorCandidates = [];
        for ($y = 1; $y < $h - 1; $y++) {
            for ($x = 1; $x < $w - 1; $x++) {
                if ($tiles[$y][$x] !== Dungeon::TILE_FLOOR) {
                    continue;
                }
                $walls = 0;
                foreach ([[0, -1], [1, 0], [0, 1], [-1, 0]] as [$dx, $dy]) {
                    if ($tiles[$y + $dy][$x + $dx] === Dungeon::TILE_WALL) {
                        $walls++;
                    }
                }
                if ($walls === 2) {
                    $doorCandidates[] = [$x, $y];
                }
            }
        }
        $rng->shuffle($doorCandidates);
        $objects = [];
        $doorsPlaced = 0;
        foreach ($doorCandidates as [$dx, $dy]) {
            if ($doorsPlaced >= 4 + $depth) {
                break;
            }
            // Only place door if it separates rooms-ish (horizontal or vertical corridor)
            $horiz = $tiles[$dy][$dx - 1] === Dungeon::TILE_FLOOR && $tiles[$dy][$dx + 1] === Dungeon::TILE_FLOOR
                && $tiles[$dy - 1][$dx] === Dungeon::TILE_WALL && $tiles[$dy + 1][$dx] === Dungeon::TILE_WALL;
            $vert = $tiles[$dy - 1][$dx] === Dungeon::TILE_FLOOR && $tiles[$dy + 1][$dx] === Dungeon::TILE_FLOOR
                && $tiles[$dy][$dx - 1] === Dungeon::TILE_WALL && $tiles[$dy][$dx + 1] === Dungeon::TILE_WALL;
            if ($horiz || $vert) {
                $tiles[$dy][$dx] = Dungeon::TILE_DOOR_CLOSED;
                $objects[] = ['x' => $dx, 'y' => $dy, 'type' => GameObject::TYPE_DOOR];
                $doorsPlaced++;
            }
        }

        $spawn = $rooms[0];
        $spawnX = $spawn[0] + intdiv($spawn[2], 2);
        $spawnY = $spawn[1] + intdiv($spawn[3], 2);

        $last = $rooms[count($rooms) - 1];
        $stairsX = $last[0] + intdiv($last[2], 2);
        $stairsY = $last[1] + intdiv($last[3], 2);
        $tiles[$stairsY][$stairsX] = Dungeon::TILE_STAIRS_DOWN;
        $objects[] = ['x' => $stairsX, 'y' => $stairsY, 'type' => GameObject::TYPE_STAIRS_DOWN];

        // Stairs up near spawn for depth > 1
        if ($depth > 1) {
            $tiles[$spawnY][$spawnX] = Dungeon::TILE_STAIRS_UP;
            $objects[] = ['x' => $spawnX, 'y' => $spawnY, 'type' => GameObject::TYPE_STAIRS_UP];
            // Move spawn one tile away
            $spawnX++;
        }

        // Chests / barrels
        for ($i = 1; $i < count($rooms) - 1; $i++) {
            $cx = $rooms[$i][0] + 1;
            $cy = $rooms[$i][1] + 1;
            if ($tiles[$cy][$cx] === Dungeon::TILE_FLOOR) {
                $type = $rng->generateRnd(100) < 50 ? GameObject::TYPE_CHEST : GameObject::TYPE_BARREL;
                $objects[] = ['x' => $cx, 'y' => $cy, 'type' => $type];
            }
        }

        $monsters = [];
        $monsterTypes = $depth >= 3 ? ['zombie', 'skeleton', 'fallen', 'archer'] : ($depth >= 2 ? ['zombie', 'skeleton', 'archer'] : ['zombie', 'fallen']);
        $count = 4 + $depth * 2;
        $floorTiles = [];
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                if ($tiles[$y][$x] === Dungeon::TILE_FLOOR
                    && !($x === $spawnX && $y === $spawnY)
                    && !($x === $stairsX && $y === $stairsY)
                ) {
                    $floorTiles[] = [$x, $y];
                }
            }
        }
        $rng->shuffle($floorTiles);
        for ($i = 0; $i < min($count, count($floorTiles)); $i++) {
            [$mx, $my] = $floorTiles[$i];
            $type = $monsterTypes[$rng->generateRnd(count($monsterTypes))];
            $monsters[] = ['x' => $mx, 'y' => $my, 'type' => $type];
        }

        $items = [];
        if (count($floorTiles) > $count + 2) {
            [$ix, $iy] = $floorTiles[$count + 1];
            $items[] = ['x' => $ix, 'y' => $iy, 'item' => 'gold'];
        }

        $dungeon = new Dungeon($tiles, $spawnX, $spawnY);
        $dungeon->stairsDownX = $stairsX;
        $dungeon->stairsDownY = $stairsY;
        if ($depth > 1) {
            $dungeon->stairsUpX = $spawnX - 1;
            $dungeon->stairsUpY = $spawnY;
        }

        return [
            'dungeon' => $dungeon,
            'monsters' => $monsters,
            'objects' => $objects,
            'items' => $items,
        ];
    }

    /**
     * Fixed town map.
     * @return array{dungeon: Dungeon, towners: list<array{x:int,y:int,role:string,name:string}>}
     */
    public function generateTown(): array
    {
        $ascii = [
            '############################',
            '#..........................#',
            '#..........................#',
            '#......########............#',
            '#......#......#............#',
            '#......#......#............#',
            '#......########............#',
            '#..........................#',
            '#..........................#',
            '#............##............#',
            '#............##............#',
            '#..........................#',
            '#..######..........######..#',
            '#..#....#..........#....#..#',
            '#..#....#..........#....#..#',
            '#..######..........######..#',
            '#..........................#',
            '#..........................#',
            '#............S.............#',
            '############################',
        ];
        $h = count($ascii);
        $w = strlen($ascii[0]);
        $tiles = [];
        $stairsX = 13;
        $stairsY = 18;
        for ($y = 0; $y < $h; $y++) {
            $row = [];
            for ($x = 0; $x < $w; $x++) {
                $ch = $ascii[$y][$x];
                if ($ch === '#') {
                    $row[] = Dungeon::TILE_WALL;
                } elseif ($ch === 'S') {
                    $row[] = Dungeon::TILE_STAIRS_DOWN;
                    $stairsX = $x;
                    $stairsY = $y;
                } else {
                    $row[] = Dungeon::TILE_FLOOR;
                }
            }
            $tiles[] = $row;
        }
        $dungeon = new Dungeon($tiles, 4, 4);
        $dungeon->stairsDownX = $stairsX;
        $dungeon->stairsDownY = $stairsY;
        $towners = [
            ['x' => 6, 'y' => 5, 'role' => 'healer', 'name' => 'Pepin'],
            ['x' => 14, 'y' => 5, 'role' => 'merchant', 'name' => 'Wirt'],
            ['x' => 22, 'y' => 14, 'role' => 'blacksmith', 'name' => 'Griswold'],
        ];

        return ['dungeon' => $dungeon, 'towners' => $towners];
    }

    /** @param list<list<int>> $tiles */
    private function carveCorridor(array &$tiles, int $x1, int $y1, int $x2, int $y2, Rng $rng): void
    {
        $x = $x1;
        $y = $y1;
        while ($x !== $x2 || $y !== $y2) {
            $tiles[$y][$x] = Dungeon::TILE_FLOOR;
            if ($rng->flipCoin(50) && $x !== $x2) {
                $x += $x < $x2 ? 1 : -1;
            } elseif ($y !== $y2) {
                $y += $y < $y2 ? 1 : -1;
            } elseif ($x !== $x2) {
                $x += $x < $x2 ? 1 : -1;
            }
        }
        $tiles[$y2][$x2] = Dungeon::TILE_FLOOR;
    }
}
