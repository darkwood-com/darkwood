<?php

declare(strict_types=1);

namespace App\Domain\CellularAutomaton;

final class Frame
{
    public function __construct(
        private readonly int $index,
        private readonly Grid $grid,
    ) {
    }

    public function index(): int
    {
        return $this->index;
    }

    public function grid(): Grid
    {
        return $this->grid;
    }

    /**
     * @return array{frame:int,size:int,cells:list<list<int>>}
     */
    public function toArray(): array
    {
        return [
            'frame' => $this->index,
            'size' => $this->grid->size(),
            'cells' => $this->grid->toBinaryMatrix(),
        ];
    }
}
