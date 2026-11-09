<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class Population
{
    /**
     * @param list<Individual> $individuals
     */
    public function __construct(
        public array $individuals,
    ) {
    }

    public function size(): int
    {
        return count($this->individuals);
    }
}
