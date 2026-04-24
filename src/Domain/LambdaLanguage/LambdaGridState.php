<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;

final class LambdaGridState
{
    /** @var list<Frame> */
    private array $frames;

    private int $frameIndex = 0;

    public function __construct(
        private Grid $grid,
        private readonly GameOfLifeStepper $stepper,
        private readonly int $maxFrames,
    ) {
        $this->frames = [new Frame($this->frameIndex, $this->grid)];
    }

    public function grid(): Grid
    {
        return $this->grid;
    }

    public function birth(int $x, int $y): void
    {
        $this->grid = $this->grid->withCellState($x, $y, \App\Domain\CellularAutomaton\CellState::Alive);
    }

    public function kill(int $x, int $y): void
    {
        $this->grid = $this->grid->withCellState($x, $y, \App\Domain\CellularAutomaton\CellState::Dead);
    }

    public function paint(int $x, int $y, string $color): void
    {
        if (strtolower($color) === '#000000') {
            $this->kill($x, $y);

            return;
        }

        $this->birth($x, $y);
    }

    public function next(): void
    {
        if (\count($this->frames) >= $this->maxFrames) {
            throw new ProgramValidationException('Maximum frame limit reached.');
        }

        $this->grid = $this->stepper->next($this->grid);
        ++$this->frameIndex;
        $this->frames[] = new Frame($this->frameIndex, $this->grid);
    }

    /**
     * @return list<Frame>
     */
    public function frames(): array
    {
        return $this->frames;
    }
}
