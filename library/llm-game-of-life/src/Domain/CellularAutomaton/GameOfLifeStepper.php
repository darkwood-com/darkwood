<?php

declare(strict_types=1);

namespace App\Domain\CellularAutomaton;

final class GameOfLifeStepper
{
    public function next(Grid $grid): Grid
    {
        $next = Grid::empty($grid->size());

        for ($y = 0; $y < $grid->size(); ++$y) {
            for ($x = 0; $x < $grid->size(); ++$x) {
                $aliveNeighbors = $grid->aliveNeighborCount($x, $y);
                $isAlive = $grid->isAlive($x, $y);

                $nextState = match (true) {
                    $isAlive && ($aliveNeighbors < 2 || $aliveNeighbors > 3) => CellState::Dead,
                    $isAlive && ($aliveNeighbors === 2 || $aliveNeighbors === 3) => CellState::Alive,
                    !$isAlive && $aliveNeighbors === 3 => CellState::Alive,
                    default => CellState::Dead,
                };

                $next = $next->withCellState($x, $y, $nextState);
            }
        }

        return $next;
    }
}
