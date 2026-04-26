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
        $tester = new CommandTester($this->createCommand());

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
        self::assertArrayHasKey('best_frame', $first);
        self::assertArrayHasKey('cells', $first['best_frame']);
    }

    public function testItStreamsBeaconDatasetChunksAsNdjson(): void
    {
        $tester = new CommandTester($this->createCommand());

        $status = $tester->execute([
            '--dataset' => 'beacon',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '42',
            '--max-frames' => '6',
        ]);

        self::assertSame(Command::SUCCESS, $status);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $tester->getDisplay()))));
        self::assertCount(2, $lines);
        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('beacon', $first['dataset']);
        self::assertSame(0, $first['generation']);
        self::assertArrayHasKey('best_fitness', $first);
        self::assertArrayHasKey('best_frame', $first);
        self::assertArrayHasKey('cells', $first['best_frame']);
    }

    private function createCommand(): TrainSupervisedCommand
    {
        $random = new MtRandomGenerator();
        $service = new SupervisedTrainingService(
            new FlowProgramExecutor(
                new LambdaEvaluationService(new GameOfLifePrimitiveInvoker(), ['birth', 'kill', 'paint', 'next', 'mirror']),
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

        return new TrainSupervisedCommand(
            $service,
            $this->createLogger(),
            $this->projectDir(),
        );
    }

    private function createLogger(): LifeRunLogger
    {
        return new LifeRunLogger(
            new WorkflowRunner(),
            new LogRunEventAction(sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson'),
            new \App\Infrastructure\Navi\LogGenerationAction(sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson'),
            new \App\Infrastructure\Navi\LogChunkAction(sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson'),
            new \App\Infrastructure\Navi\LogBenchmarkAction(sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson'),
        );
    }

    private function projectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
