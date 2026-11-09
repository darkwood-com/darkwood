<?php

declare(strict_types=1);

namespace App\Tests\Domain\CellularAutomaton;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\Grid;
use PHPUnit\Framework\TestCase;

final class FrameTest extends TestCase
{
    public function testItSerializesFrameToArray(): void
    {
        $grid = Grid::fromAliveCoordinates(3, [
            ['x' => 0, 'y' => 0],
            ['x' => 1, 'y' => 1],
        ]);

        $frame = new Frame(7, $grid);
        $data = $frame->toArray();

        self::assertSame(7, $data['frame']);
        self::assertSame(3, $data['size']);
        self::assertSame(
            [
                [1, 0, 0],
                [0, 1, 0],
                [0, 0, 0],
            ],
            $data['cells'],
        );
    }
}
