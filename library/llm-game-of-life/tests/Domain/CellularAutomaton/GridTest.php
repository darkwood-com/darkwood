<?php

declare(strict_types=1);

namespace App\Tests\Domain\CellularAutomaton;

use App\Domain\CellularAutomaton\CellState;
use App\Domain\CellularAutomaton\Grid;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class GridTest extends TestCase
{
    public function testItCanCreateGridFromAliveCoordinates(): void
    {
        $grid = Grid::fromAliveCoordinates(4, [
            ['x' => 1, 'y' => 1],
            ['x' => 2, 'y' => 2],
        ]);

        self::assertTrue($grid->isAlive(1, 1));
        self::assertTrue($grid->isAlive(2, 2));
        self::assertFalse($grid->isAlive(0, 0));
    }

    public function testItCountsAliveNeighbors(): void
    {
        $grid = Grid::fromAliveCoordinates(4, [
            ['x' => 0, 'y' => 1],
            ['x' => 1, 'y' => 0],
            ['x' => 1, 'y' => 1],
        ]);

        self::assertSame(3, $grid->aliveNeighborCount(0, 0));
    }

    public function testItReturnsNewGridWhenMutatingCellState(): void
    {
        $grid = Grid::empty(3);
        $updated = $grid->withCellState(1, 1, CellState::Alive);

        self::assertFalse($grid->isAlive(1, 1));
        self::assertTrue($updated->isAlive(1, 1));
    }

    public function testItRejectsOutOfBoundsCoordinates(): void
    {
        $grid = Grid::empty(3);

        $this->expectException(InvalidArgumentException::class);
        $grid->cellStateAt(3, 0);
    }
}
