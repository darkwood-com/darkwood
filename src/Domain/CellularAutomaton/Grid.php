<?php

declare(strict_types=1);

namespace App\Domain\CellularAutomaton;

use InvalidArgumentException;

final class Grid
{
    /** @var list<list<CellState>> */
    private array $cells;

    /**
     * @param list<list<CellState>> $cells
     */
    private function __construct(
        private readonly int $size,
        array $cells,
    ) {
        $this->assertCellShape($cells);
        $this->cells = $cells;
    }

    public static function empty(int $size): self
    {
        if ($size <= 0) {
            throw new InvalidArgumentException('Grid size must be positive.');
        }

        $row = array_fill(0, $size, CellState::Dead);
        $cells = array_fill(0, $size, $row);

        return new self($size, $cells);
    }

    /**
     * @param list<array{x:int,y:int}> $coordinates
     */
    public static function fromAliveCoordinates(int $size, array $coordinates): self
    {
        $grid = self::empty($size);

        foreach ($coordinates as $coordinate) {
            $grid = $grid->withCellState($coordinate['x'], $coordinate['y'], CellState::Alive);
        }

        return $grid;
    }

    public function size(): int
    {
        return $this->size;
    }

    public function cellStateAt(int $x, int $y): CellState
    {
        $this->assertInBounds($x, $y);

        return $this->cells[$y][$x];
    }

    public function isAlive(int $x, int $y): bool
    {
        return $this->cellStateAt($x, $y)->isAlive();
    }

    public function withCellState(int $x, int $y, CellState $state): self
    {
        $this->assertInBounds($x, $y);

        $cells = $this->cells;
        $cells[$y][$x] = $state;

        return new self($this->size, $cells);
    }

    public function aliveNeighborCount(int $x, int $y): int
    {
        $this->assertInBounds($x, $y);

        $count = 0;

        for ($dy = -1; $dy <= 1; ++$dy) {
            for ($dx = -1; $dx <= 1; ++$dx) {
                if (0 === $dx && 0 === $dy) {
                    continue;
                }

                $neighborX = $x + $dx;
                $neighborY = $y + $dy;

                if (!$this->isCoordinateInBounds($neighborX, $neighborY)) {
                    continue;
                }

                if ($this->cells[$neighborY][$neighborX]->isAlive()) {
                    ++$count;
                }
            }
        }

        return $count;
    }

    /**
     * @return list<list<int>>
     */
    public function toBinaryMatrix(): array
    {
        return array_map(
            static fn (array $row): array => array_map(
                static fn (CellState $state): int => $state->value,
                $row,
            ),
            $this->cells,
        );
    }

    /**
     * @param list<list<CellState>> $cells
     */
    private function assertCellShape(array $cells): void
    {
        if (\count($cells) !== $this->size) {
            throw new InvalidArgumentException('Grid rows count must match size.');
        }

        foreach ($cells as $row) {
            if (\count($row) !== $this->size) {
                throw new InvalidArgumentException('Grid columns count must match size.');
            }

            foreach ($row as $state) {
                if (!$state instanceof CellState) {
                    throw new InvalidArgumentException('Grid must contain only CellState values.');
                }
            }
        }
    }

    private function assertInBounds(int $x, int $y): void
    {
        if (!$this->isCoordinateInBounds($x, $y)) {
            throw new InvalidArgumentException(\sprintf('Coordinate (%d,%d) is out of bounds.', $x, $y));
        }
    }

    private function isCoordinateInBounds(int $x, int $y): bool
    {
        return $x >= 0 && $x < $this->size && $y >= 0 && $y < $this->size;
    }
}
