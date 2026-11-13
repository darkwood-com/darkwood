<?php

declare(strict_types=1);

namespace App\Domain\Evolution;

final readonly class EvolutionEngine
{
    public function __construct(
        private TournamentSelector $selector,
        private AstCrossoverOperator $crossover,
        private AstMutationOperator $mutation,
        private RandomGeneratorInterface $random,
    ) {
    }

    /**
     * @return list<array{type:string,nodes:list<array<string,mixed>>}>
     */
    public function nextPopulation(
        Population $population,
        int $targetSize,
        int $gridSize,
        float $mutationRate = 0.2,
        float $crossoverRate = 0.7,
        float $eliteRate = 0.1,
        int $tournamentSize = 3,
    ): array {
        if ($population->size() === 0) {
            throw new \InvalidArgumentException('Population cannot be empty.');
        }

        $sorted = $population->individuals;
        usort($sorted, static fn (Individual $a, Individual $b): int => $b->fitness <=> $a->fitness);

        $eliteCount = max(1, (int) floor($targetSize * $eliteRate));
        $next = [];
        for ($i = 0; $i < $eliteCount && $i < count($sorted); ++$i) {
            $next[] = $sorted[$i]->programAst;
        }

        while (count($next) < $targetSize) {
            $parentA = $this->selector->select($population, $tournamentSize);
            $parentB = $this->selector->select($population, $tournamentSize);

            $children = [$parentA->programAst, $parentB->programAst];
            if ($this->random->float() <= $crossoverRate) {
                $children = $this->crossover->crossover($parentA->programAst, $parentB->programAst);
            }

            foreach ($children as $child) {
                if ($this->random->float() <= $mutationRate) {
                    $child = $this->mutation->mutate($child, $gridSize);
                }

                $next[] = $child;
                if (count($next) >= $targetSize) {
                    break;
                }
            }
        }

        return array_values(array_slice($next, 0, $targetSize));
    }
}
