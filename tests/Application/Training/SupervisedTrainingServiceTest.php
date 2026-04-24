<?php

declare(strict_types=1);

namespace App\Tests\Application\Training;

use App\Application\Training\SupervisedTrainingService;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\Evolution\AstCrossoverOperator;
use App\Domain\Evolution\AstMutationOperator;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\Evolution\TournamentSelector;
use App\Domain\LambdaLanguage\LambdaEvaluationService;
use App\Infrastructure\Evolution\MtRandomGenerator;
use App\Infrastructure\LambdaLanguage\FlowProgramExecutor;
use App\Infrastructure\LambdaLanguage\GameOfLifePrimitiveInvoker;
use PHPUnit\Framework\TestCase;

final class SupervisedTrainingServiceTest extends TestCase
{
    public function testItIsDeterministicForGivenSeed(): void
    {
        $dataset = json_decode(
            (string) file_get_contents('/Users/math/Sites/darkwood/llm-game-of-life/data/patterns/block.json'),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $service = $this->createService();
        $first = iterator_to_array($service->train($dataset, 'block', 6, 2, 42, 6), false);
        $second = iterator_to_array($service->train($dataset, 'block', 6, 2, 42, 6), false);

        self::assertSame($first, $second);
    }

    private function createService(): SupervisedTrainingService
    {
        $random = new MtRandomGenerator();

        return new SupervisedTrainingService(
            new FlowProgramExecutor(
                new LambdaEvaluationService(new GameOfLifePrimitiveInvoker(), ['birth', 'kill', 'paint', 'next']),
                new GameOfLifeStepper(),
            ),
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
