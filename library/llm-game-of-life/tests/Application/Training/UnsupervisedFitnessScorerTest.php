<?php

declare(strict_types=1);

namespace App\Tests\Application\Training;

use App\Application\Training\UnsupervisedFitnessScorer;
use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\Grid;
use PHPUnit\Framework\TestCase;

final class UnsupervisedFitnessScorerTest extends TestCase
{
    public function testItPenalizesAllDeadAndAllAliveTraces(): void
    {
        $scorer = new UnsupervisedFitnessScorer();

        self::assertSame(0.0, $scorer->score([
            new Frame(0, Grid::empty(4)),
            new Frame(1, Grid::empty(4)),
            new Frame(2, Grid::empty(4)),
        ], 4));

        $allAlive = Grid::fromAliveCoordinates(4, $this->allCoordinates(4));
        self::assertSame(0.0, $scorer->score([
            new Frame(0, $allAlive),
            new Frame(1, $allAlive),
            new Frame(2, $allAlive),
        ], 4));
    }

    public function testItRewardsMotionOverStaticPatterns(): void
    {
        $scorer = new UnsupervisedFitnessScorer();

        $static = Grid::fromAliveCoordinates(5, [
            ['x' => 1, 'y' => 1],
            ['x' => 2, 'y' => 1],
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
        ]);

        $vertical = Grid::fromAliveCoordinates(5, [
            ['x' => 2, 'y' => 1],
            ['x' => 2, 'y' => 2],
            ['x' => 2, 'y' => 3],
        ]);
        $horizontal = Grid::fromAliveCoordinates(5, [
            ['x' => 1, 'y' => 2],
            ['x' => 2, 'y' => 2],
            ['x' => 3, 'y' => 2],
        ]);

        $staticScore = $scorer->score([
            new Frame(0, $static),
            new Frame(1, $static),
            new Frame(2, $static),
        ], 5);
        $movingScore = $scorer->score([
            new Frame(0, $vertical),
            new Frame(1, $horizontal),
            new Frame(2, $vertical),
        ], 5);

        self::assertGreaterThan($staticScore, $movingScore);
        self::assertGreaterThanOrEqual(0.0, $movingScore);
        self::assertLessThanOrEqual(1.0, $movingScore);
    }

    /**
     * @return list<array{x:int,y:int}>
     */
    private function allCoordinates(int $size): array
    {
        $coordinates = [];
        for ($y = 0; $y < $size; ++$y) {
            for ($x = 0; $x < $size; ++$x) {
                $coordinates[] = ['x' => $x, 'y' => $y];
            }
        }

        return $coordinates;
    }
}
