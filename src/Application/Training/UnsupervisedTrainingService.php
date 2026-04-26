<?php

declare(strict_types=1);

namespace App\Application\Training;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\Evolution\Individual;
use App\Domain\Evolution\Population;
use App\Domain\Evolution\RandomGeneratorInterface;
use App\Domain\CellularAutomaton\Grid;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use App\Domain\LambdaLanguage\ProgramValidator;

final class UnsupervisedTrainingService
{
    public function __construct(
        private readonly ProgramExecutorInterface $executor,
        private readonly EvolutionEngine $evolution,
        private readonly RandomGeneratorInterface $random,
    ) {
    }

    /**
     * @return \Generator<int,array{generation:int,best_fitness:float,average_fitness:float,best_program:array<string,mixed>,best_frame:array{frame:int,size:int,cells:list<list<int>>},seed:int,population:int}>
     */
    public function train(
        int $size,
        int $population,
        int $generations,
        int $seed,
        int $maxFrames,
    ): \Generator {
        $this->random->seed($seed);
        $validator = new ProgramValidator(gridSize: $size);
        $programs = [];

        for ($i = 0; $i < $population; ++$i) {
            $programs[] = $this->randomProgramAst($size);
        }

        for ($generation = 0; $generation < $generations; ++$generation) {
            $scored = [];
            $individuals = [];
            foreach ($programs as $programAst) {
                try {
                    $program = Program::fromArray($programAst, $validator);
                    $frames = $this->executor->execute($program, Grid::empty($size), $maxFrames);
                    $fitness = $this->fitness($frames, $size);
                } catch (\Throwable) {
                    $frames = [new Frame(0, Grid::empty($size))];
                    $fitness = 0.0;
                }

                $scored[] = [
                    'ast' => $programAst,
                    'fitness' => $fitness,
                    'frames' => $frames,
                ];
                $individuals[] = new Individual($programAst, $fitness);
            }

            usort($scored, static fn (array $a, array $b): int => $b['fitness'] <=> $a['fitness']);
            $best = $scored[0];
            $averageFitness = array_sum(array_column($scored, 'fitness')) / max(1, count($scored));

            /** @var list<Frame> $bestFrames */
            $bestFrames = $best['frames'];
            $bestFrame = $bestFrames[array_key_last($bestFrames)];

            yield [
                'generation' => $generation,
                'best_fitness' => round((float) $best['fitness'], 6),
                'average_fitness' => round($averageFitness, 6),
                'best_program' => $best['ast'],
                'best_frame' => $bestFrame->toArray(),
                'seed' => $seed,
                'population' => $population,
            ];

            $programs = $this->evolution->nextPopulation(
                new Population($individuals),
                $population,
                $size,
            );
        }
    }

    /**
     * @param list<Frame> $frames
     */
    private function fitness(array $frames, int $size): float
    {
        $last = $frames[array_key_last($frames)]->grid()->toBinaryMatrix();
        $alive = 0;
        foreach ($last as $row) {
            $alive += array_sum($row);
        }

        $totalCells = $size * $size;
        $aliveRatio = $alive / max(1, $totalCells);
        $entropyScore = 1.0 - abs(0.5 - $aliveRatio) * 2.0;

        $motionScore = 0.0;
        if (count($frames) > 1) {
            $previous = $frames[count($frames) - 2]->grid()->toBinaryMatrix();
            $changes = 0;
            for ($y = 0; $y < $size; ++$y) {
                for ($x = 0; $x < $size; ++$x) {
                    if ($previous[$y][$x] !== $last[$y][$x]) {
                        ++$changes;
                    }
                }
            }
            $motionScore = $changes / max(1, $totalCells);
        }

        $lifetimeScore = min(1.0, count($frames) / 20.0);

        return (0.5 * $entropyScore) + (0.3 * $motionScore) + (0.2 * $lifetimeScore);
    }

    /**
     * @return array{type:string,nodes:list<array<string,mixed>>}
     */
    private function randomProgramAst(int $size): array
    {
        $nodes = [];
        $instructionCount = $this->random->int(2, 8);

        for ($i = 0; $i < $instructionCount; ++$i) {
            $kind = $this->random->int(0, 4);
            $x = $this->random->int(0, $size - 1);
            $y = $this->random->int(0, $size - 1);

            $nodes[] = match ($kind) {
                0 => ['type' => 'birth', 'x' => $x, 'y' => $y],
                1 => ['type' => 'kill', 'x' => $x, 'y' => $y],
                2 => ['type' => 'paint', 'x' => $x, 'y' => $y, 'color' => $this->random->int(0, 1) ? '#ffffff' : '#000000'],
                3 => ['type' => 'mirror', 'axis' => $this->random->int(0, 1) === 0 ? 'horizontal' : 'vertical'],
                default => ['type' => 'next'],
            };
        }

        if (!in_array('next', array_column($nodes, 'type'), true)) {
            $nodes[] = ['type' => 'next'];
        }

        return [
            'type' => 'sequence',
            'nodes' => $nodes,
        ];
    }

}
