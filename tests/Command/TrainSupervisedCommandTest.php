<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Training\SupervisedTrainingService;
use App\Command\TrainSupervisedCommand;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\Evolution\AstCrossoverOperator;
use App\Domain\Evolution\AstMutationOperator;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\Evolution\TournamentSelector;
use App\Domain\LambdaLanguage\LambdaEvaluationService;
use App\Infrastructure\Evolution\MtRandomGenerator;
use App\Infrastructure\LambdaLanguage\FlowProgramExecutor;
use App\Infrastructure\LambdaLanguage\GameOfLifePrimitiveInvoker;
use App\Infrastructure\Navi\LifeRunLogger;
use App\Infrastructure\Navi\LogRunEventAction;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class TrainSupervisedCommandTest extends TestCase
{
    public function testItStreamsGenerationChunksAsNdjson(): void
    {
        $random = new MtRandomGenerator();
        $service = new SupervisedTrainingService(
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

        $command = new TrainSupervisedCommand(
            $service,
            $this->createLogger(),
            '/Users/math/Sites/darkwood/llm-game-of-life',
        );
        $tester = new CommandTester($command);

        $status = $tester->execute([
            '--dataset' => 'block',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '42',
            '--max-frames' => '6',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $tester->getDisplay()))));
        self::assertCount(2, $lines);
        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('block', $first['dataset']);
        self::assertSame(0, $first['generation']);
        self::assertArrayHasKey('best_fitness', $first);
    }

    private function createLogger(): LifeRunLogger
    {
        return new LifeRunLogger(
            new WorkflowRunner(),
            new LogRunEventAction(sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson'),
        );
    }
}
