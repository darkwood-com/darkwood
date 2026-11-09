<?php

declare(strict_types=1);

namespace App\Tests\Data;

use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PatternDatasetTest extends TestCase
{
    #[DataProvider('datasetFiles')]
    public function testPatternDatasetIsReadableAndRectangular(string $path): void
    {
        $dataset = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        self::assertIsArray($dataset);
        self::assertArrayHasKey('name', $dataset);
        self::assertArrayHasKey('size', $dataset);
        self::assertArrayHasKey('target_frames', $dataset);
        self::assertIsInt($dataset['size']);
        self::assertGreaterThan(0, $dataset['size']);
        self::assertIsArray($dataset['target_frames']);
        self::assertNotEmpty($dataset['target_frames']);

        foreach ($dataset['target_frames'] as $frame) {
            self::assertIsArray($frame);
            self::assertCount($dataset['size'], $frame);

            foreach ($frame as $row) {
                self::assertIsArray($row);
                self::assertCount($dataset['size'], $row);

                foreach ($row as $cell) {
                    self::assertContains($cell, [0, 1]);
                }
            }
        }
    }

    public function testBeaconSecondFrameMatchesGameOfLifeStep(): void
    {
        $dataset = json_decode(
            (string) file_get_contents(\dirname(__DIR__, 2) . '/data/patterns/beacon.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertIsArray($dataset);
        $frames = $dataset['target_frames'];
        self::assertIsArray($frames);

        $initialGrid = Grid::fromAliveCoordinates((int) $dataset['size'], $this->aliveCoordinates($frames[0]));
        $next = (new GameOfLifeStepper())->next($initialGrid);

        self::assertSame($frames[1], $next->toBinaryMatrix());
    }

    /**
     * @return iterable<string,array{string}>
     */
    public static function datasetFiles(): iterable
    {
        $paths = glob(\dirname(__DIR__, 2) . '/data/patterns/*.json') ?: [];

        foreach ($paths as $path) {
            yield pathinfo($path, \PATHINFO_FILENAME) => [$path];
        }
    }

    /**
     * @param list<list<int>> $matrix
     *
     * @return list<array{x:int,y:int}>
     */
    private function aliveCoordinates(array $matrix): array
    {
        $coordinates = [];
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell === 1) {
                    $coordinates[] = ['x' => $x, 'y' => $y];
                }
            }
        }

        return $coordinates;
    }
}
