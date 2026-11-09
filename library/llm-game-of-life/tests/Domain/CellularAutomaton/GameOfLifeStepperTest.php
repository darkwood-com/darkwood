<?php

declare(strict_types=1);

namespace App\Tests\Domain\CellularAutomaton;

use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;
use PHPUnit\Framework\TestCase;

final class GameOfLifeStepperTest extends TestCase
{
    public function testBlinkerOscillatorMovesToHorizontalState(): void
    {
        $grid = Grid::fromAliveCoordinates(5, [
            ['x' => 2, 'y' => 1],
            ['x' => 2, 'y' => 2],
            ['x' => 2, 'y' => 3],
        ]);

        $next = (new GameOfLifeStepper())->next($grid);

        self::assertTrue($next->isAlive(1, 2));
        self::assertTrue($next->isAlive(2, 2));
        self::assertTrue($next->isAlive(3, 2));
        self::assertFalse($next->isAlive(2, 1));
        self::assertFalse($next->isAlive(2, 3));
    }

    public function testBlockStillLifeStaysStable(): void
    {
        $grid = Grid::fromAliveCoordinates(4, [
            ['x' => 1, 'y' => 1],
            ['x' => 2, 'y' => 1],
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
        ]);

        $next = (new GameOfLifeStepper())->next($grid);

        self::assertSame($grid->toBinaryMatrix(), $next->toBinaryMatrix());
    }
}
