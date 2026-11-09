<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class TournamentSelector
{
    public function __construct(
        private RandomGeneratorInterface $random,
    ) {
    }

    public function select(Population $population, int $tournamentSize): Individual
    {
        if ($population->size() === 0) {
            throw new \InvalidArgumentException('Population cannot be empty.');
        }

        $best = null;
        $draws = max(1, $tournamentSize);

        for ($i = 0; $i < $draws; ++$i) {
            $candidate = $population->individuals[$this->random->int(0, $population->size() - 1)];
            if ($best === null || $candidate->fitness > $best->fitness) {
                $best = $candidate;
            }
        }

        return $best;
    }
}
