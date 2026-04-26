<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Training\UnsupervisedTrainingService;
use App\Command\TrainUnsupervisedCommand;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\Evolution\AstCrossoverOperator;
use App\Domain\Evolution\AstMutationOperator;
use App\Domain\Evolution\EvolutionEngine;
use App\Domain\LambdaLanguage\LambdaEvaluationService;
use App\Domain\Evolution\TournamentSelector;
use App\Infrastructure\Evolution\MtRandomGenerator;
use App\Infrastructure\LambdaLanguage\FlowProgramExecutor;
use App\Infrastructure\LambdaLanguage\GameOfLifePrimitiveInvoker;
use App\Infrastructure\Navi\LifeRunLogger;
use App\Infrastructure\Navi\LogRunEventAction;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class TrainUnsupervisedCommandTest extends TestCase
{
    public function testItStreamsGenerationChunksAsNdjson(): void
    {
        $service = new UnsupervisedTrainingService(
            new FlowProgramExecutor(
                new LambdaEvaluationService(
                    new GameOfLifePrimitiveInvoker(),
                    ['birth', 'kill', 'paint', 'next', 'mirror'],
                ),
                new GameOfLifeStepper(),
            ),
            new EvolutionEngine(
                new TournamentSelector($random = new MtRandomGenerator()),
                new AstCrossoverOperator($random),
                new AstMutationOperator($random),
                $random,
            ),
            $random,
        );
        $command = new TrainUnsupervisedCommand($service, $this->createLogger());
        $tester = new CommandTester($command);

        $status = $tester->execute([
            '--size' => '8',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '123',
            '--max-frames' => '6',
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $lines = array_values(array_filter(array_map('trim', explode("\n", $tester->getDisplay()))));
        self::assertCount(2, $lines);

        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(0, $first['generation']);
        self::assertSame(123, $first['seed']);
        self::assertArrayHasKey('best_program', $first);
        self::assertArrayHasKey('best_frame', $first);
    }

    public function testItWritesNdjsonAndSummaryFilesWhenOutputIsProvided(): void
    {
        $service = new UnsupervisedTrainingService(
            new FlowProgramExecutor(
                new LambdaEvaluationService(
                    new GameOfLifePrimitiveInvoker(),
                    ['birth', 'kill', 'paint', 'next', 'mirror'],
                ),
                new GameOfLifeStepper(),
            ),
            new EvolutionEngine(
                new TournamentSelector($random = new MtRandomGenerator()),
                new AstCrossoverOperator($random),
                new AstMutationOperator($random),
                $random,
            ),
            $random,
        );
        $command = new TrainUnsupervisedCommand($service, $this->createLogger());
        $tester = new CommandTester($command);

        $baseDir = sys_get_temp_dir() . '/llm_game_of_life_tests';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        $outputPath = $baseDir . '/unsupervised.ndjson';
        @unlink($outputPath);
        @unlink($baseDir . '/unsupervised.summary.json');

        $status = $tester->execute([
            '--size' => '8',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '123',
            '--max-frames' => '6',
            '--output' => $outputPath,
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($outputPath);
        self::assertFileExists($baseDir . '/unsupervised.summary.json');
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
}
