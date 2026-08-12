<?php

declare(strict_types=1);

namespace Diablo;

/**
 * Orthogonal tile dungeon. tiles[y][x]: 0=floor, 1=wall, 2=doorClosed, 3=doorOpen.
 */
final class Dungeon
{
    public const TILE_FLOOR = 0;
    public const TILE_WALL = 1;
    public const TILE_DOOR_CLOSED = 2;
    public const TILE_DOOR_OPEN = 3;
    public const TILE_STAIRS_DOWN = 4;
    public const TILE_STAIRS_UP = 5;

    /** @var list<list<int>> */
    private array $tiles;

    /** @var list<list<bool>> */
    private array $explored = [];

    /** @var list<list<bool>> */
    private array $visible = [];

    public readonly int $width;
    public readonly int $height;
    public int $spawnX;
    public int $spawnY;
    public int $stairsDownX = -1;
    public int $stairsDownY = -1;
    public int $stairsUpX = -1;
    public int $stairsUpY = -1;

    public function __construct(?array $tiles = null, int $spawnX = 2, int $spawnY = 2)
    {
        if ($tiles === null) {
            $ascii = [
                '####################',
                '#..................#',
                '#..................#',
                '#....######........#',
                '#....#.............#',
                '#....#....#........#',
                '#....######........#',
                '#..................#',
                '#..................#',
                '#........##........#',
                '#........##........#',
                '#..................#',
                '#..############....#',
                '#..#...............#',
                '#..#..........#....#',
                '#..############....#',
                '#..................#',
                '#..................#',
                '#..................#',
                '####################',
            ];
            $this->height = count($ascii);
            $this->width = strlen($ascii[0]);
            $this->tiles = [];
            for ($y = 0; $y < $this->height; $y++) {
                $row = [];
                for ($x = 0; $x < $this->width; $x++) {
                    $row[] = ($ascii[$y][$x] === '#') ? self::TILE_WALL : self::TILE_FLOOR;
                }
                $this->tiles[] = $row;
            }
            $this->spawnX = $spawnX;
            $this->spawnY = $spawnY;
        } else {
            $this->tiles = $tiles;
            $this->height = count($tiles);
            $this->width = count($tiles[0]);
            $this->spawnX = $spawnX;
            $this->spawnY = $spawnY;
        }
        $this->resetVision();
    }

    public function resetVision(): void
    {
        $this->explored = [];
        $this->visible = [];
        for ($y = 0; $y < $this->height; $y++) {
            $this->explored[$y] = array_fill(0, $this->width, false);
            $this->visible[$y] = array_fill(0, $this->width, false);
        }
    }

    public function tile(int $x, int $y): int
    {
        if (!$this->inBounds($x, $y)) {
            return self::TILE_WALL;
        }

        return $this->tiles[$y][$x];
    }

    public function setTile(int $x, int $y, int $tile): void
    {
        if ($this->inBounds($x, $y)) {
            $this->tiles[$y][$x] = $tile;
        }
    }

    public function inBounds(int $x, int $y): bool
    {
        return $x >= 0 && $y >= 0 && $x < $this->width && $y < $this->height;
    }

    public function isNotSolid(int $x, int $y): bool
    {
        if (!$this->inBounds($x, $y)) {
            return false;
        }
        $t = $this->tiles[$y][$x];

        return $t !== self::TILE_WALL && $t !== self::TILE_DOOR_CLOSED;
    }

    public function isWalkable(int $x, int $y): bool
    {
        if (!$this->inBounds($x, $y)) {
            return false;
        }
        $t = $this->tiles[$y][$x];

        return $t === self::TILE_FLOOR
            || $t === self::TILE_DOOR_OPEN
            || $t === self::TILE_STAIRS_DOWN
            || $t === self::TILE_STAIRS_UP;
    }

    public function isWall(int $x, int $y): bool
    {
        return $this->tile($x, $y) === self::TILE_WALL;
    }

    public function isDoorClosed(int $x, int $y): bool
    {
        return $this->tile($x, $y) === self::TILE_DOOR_CLOSED;
    }

    public function openDoor(int $x, int $y): void
    {
        if ($this->isDoorClosed($x, $y)) {
            $this->setTile($x, $y, self::TILE_DOOR_OPEN);
        }
    }

    public function isExplored(int $x, int $y): bool
    {
        return $this->inBounds($x, $y) && $this->explored[$y][$x];
    }

    public function isVisible(int $x, int $y): bool
    {
        return $this->inBounds($x, $y) && $this->visible[$y][$x];
    }

    public function updateVision(int $px, int $py, int $radius = 8): void
    {
        for ($y = 0; $y < $this->height; $y++) {
            for ($x = 0; $x < $this->width; $x++) {
                $this->visible[$y][$x] = false;
            }
        }
        for ($dy = -$radius; $dy <= $radius; $dy++) {
            for ($dx = -$radius; $dx <= $radius; $dx++) {
                if ($dx * $dx + $dy * $dy > $radius * $radius) {
                    continue;
                }
                $x = $px + $dx;
                $y = $py + $dy;
                if (!$this->inBounds($x, $y)) {
                    continue;
                }
                $this->visible[$y][$x] = true;
                $this->explored[$y][$x] = true;
            }
        }
    }

    /** @return list<list<int>> */
    public function rawTiles(): array
    {
        return $this->tiles;
    }
}
