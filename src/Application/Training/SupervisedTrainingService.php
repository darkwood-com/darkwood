<?php

declare(strict_types=1);

namespace App\Application\Training;

use App\Domain\CellularAutomaton\Grid;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\Evolution\Individual;
use App\Domain\Evolution\Population;
use App\Domain\Evolution\RandomGeneratorInterface;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use App\Domain\LambdaLanguage\ProgramValidator;

final class SupervisedTrainingService
{
    public function __construct(
        private readonly ProgramExecutorInterface $executor,
        private readonly EvolutionEngine $evolution,
        private readonly RandomGeneratorInterface $random,
    ) {
    }

    /**
     * @return \Generator<int,array{generation:int,best_fitness:float,average_fitness:float,best_program:array<string,mixed>,dataset:string,seed:int,population:int}>
     */
    public function train(
        array $dataset,
        string $datasetName,
        int $population,
        int $generations,
        int $seed,
        int $maxFrames,
    ): \Generator {
        $size = (int) ($dataset['size'] ?? 0);
        $targetFrames = $dataset['target_frames'] ?? null;
        if ($size <= 0 || !\is_array($targetFrames) || $targetFrames === []) {
            throw new \InvalidArgumentException('Dataset must define size and non-empty target_frames.');
        }

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
                    $fitness = $this->supervisedFitness($frames, $targetFrames, $size, \count($programAst['nodes']));
                } catch (\Throwable) {
                    $fitness = 0.0;
                }

                $scored[] = ['ast' => $programAst, 'fitness' => $fitness];
                $individuals[] = new Individual($programAst, $fitness);
            }

            usort($scored, static fn (array $a, array $b): int => $b['fitness'] <=> $a['fitness']);
            $best = $scored[0];
            $avg = array_sum(array_column($scored, 'fitness')) / max(1, \count($scored));

            yield [
                'generation' => $generation,
                'best_fitness' => round((float) $best['fitness'], 6),
                'average_fitness' => round((float) $avg, 6),
                'best_program' => $best['ast'],
                'dataset' => $datasetName,
                'seed' => $seed,
                'population' => $population,
            ];

            $programs = $this->evolution->nextPopulation(new Population($individuals), $population, $size);
        }
    }

    /**
     * @param list<\App\Domain\CellularAutomaton\Frame> $frames
     * @param list<list<list<int>>> $targetFrames
     */
    private function supervisedFitness(array $frames, array $targetFrames, int $size, int $programSize): float
    {
        $pairCount = min(\count($frames), \count($targetFrames));
        if ($pairCount === 0) {
            return 0.0;
        }

        $distanceSum = 0.0;
        for ($i = 0; $i < $pairCount; ++$i) {
            $generated = $frames[$i]->grid()->toBinaryMatrix();
            $target = $targetFrames[$i];
            $diff = 0;
            for ($y = 0; $y < $size; ++$y) {
                for ($x = 0; $x < $size; ++$x) {
                    if (($generated[$y][$x] ?? 0) !== ($target[$y][$x] ?? 0)) {
                        ++$diff;
                    }
                }
            }
            $distanceSum += $diff / max(1, $size * $size);
        }

        $normalizedDistance = $distanceSum / $pairCount;
        $fitness = 1.0 - $normalizedDistance;
        $fitness -= min(0.2, $programSize * 0.002);

        return max(0.0, min(1.0, $fitness));
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

        return ['type' => 'sequence', 'nodes' => $nodes];
    }
}
