<?php

declare(strict_types=1);

namespace App\Tests\Application\Training;

use App\Application\Training\UnsupervisedTrainingService;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use App\Domain\Evolution\AstCrossoverOperator;
use App\Domain\Evolution\AstMutationOperator;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\Evolution\TournamentSelector;
use App\Infrastructure\Evolution\MtRandomGenerator;
use PHPUnit\Framework\TestCase;

final class UnsupervisedTrainingServiceTest extends TestCase
{
    public function testItIsDeterministicForAGivenSeed(): void
    {
        $service = self::createService();

        $first = iterator_to_array($service->train(8, 8, 3, 42, 8), false);
        $second = iterator_to_array($service->train(8, 8, 3, 42, 8), false);

        self::assertSame($first, $second);
        self::assertCount(3, $first);
        self::assertArrayHasKey('best_fitness', $first[0]);
    }

    private static function createContainerExecutor(): ProgramExecutorInterface
    {
        return new \App\Infrastructure\LambdaLanguage\FlowProgramExecutor(
            new \App\Domain\LambdaLanguage\LambdaEvaluationService(
                new \App\Infrastructure\LambdaLanguage\GameOfLifePrimitiveInvoker(),
                ['birth', 'kill', 'paint', 'next'],
            ),
            new \App\Domain\CellularAutomaton\GameOfLifeStepper(),
        );
    }

    private static function createService(): UnsupervisedTrainingService
    {
        $random = new MtRandomGenerator();

        return new UnsupervisedTrainingService(
            self::createContainerExecutor(),
            new EvolutionEngine(
                new TournamentSelector($random),
                new AstCrossoverOperator($random),
                new AstMutationOperator($random),
                $random,
            ),
            $random,
        );
    }
}
