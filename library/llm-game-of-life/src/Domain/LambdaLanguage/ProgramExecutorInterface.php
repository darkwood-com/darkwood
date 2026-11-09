<?php

declare(strict_types=1);

namespace App\Domain\LambdaLanguage;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\Grid;

interface ProgramExecutorInterface
{
    /**
     * @return list<Frame>
     */
    public function execute(Program $program, Grid $initialGrid, int $maxFrames = 120): array;
}
