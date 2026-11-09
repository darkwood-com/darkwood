<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Application\Training\SupervisedTrainingService;
use App\Application\Training\UnsupervisedTrainingService;
use App\Command\BenchmarkRunCommand;
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
use App\Infrastructure\Navi\LogBenchmarkAction;
use App\Infrastructure\Navi\LogChunkAction;
use App\Infrastructure\Navi\LogGenerationAction;
use App\Infrastructure\Navi\LogRunEventAction;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class BenchmarkRunCommandTest extends TestCase
{
    public function testItRunsUnsupervisedBenchmarkAndWritesJson(): void
    {
        $randomA = new MtRandomGenerator();
        $randomB = new MtRandomGenerator();
        $executor = new FlowProgramExecutor(
            new LambdaEvaluationService(new GameOfLifePrimitiveInvoker(), ['birth', 'kill', 'paint', 'next', 'mirror']),
            new GameOfLifeStepper(),
        );

        $unsupervised = new UnsupervisedTrainingService(
            $executor,
            new EvolutionEngine(
                new TournamentSelector($randomA),
                new AstCrossoverOperator($randomA),
                new AstMutationOperator($randomA),
                $randomA,
            ),
            $randomA,
        );

        $supervised = new SupervisedTrainingService(
            $executor,
            new EvolutionEngine(
                new TournamentSelector($randomB),
                new AstCrossoverOperator($randomB),
                new AstMutationOperator($randomB),
                $randomB,
            ),
            $randomB,
        );

        $command = new BenchmarkRunCommand(
            $supervised,
            $unsupervised,
            $this->createLogger(),
            $this->projectDir(),
        );
        $tester = new CommandTester($command);

        $outputPath = sys_get_temp_dir() . '/llm_game_of_life_tests/benchmarks/test-unsupervised-result.json';
        $status = $tester->execute([
            '--mode' => 'unsupervised',
            '--size' => '8',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '42',
            '--max-frames' => '6',
            '--output' => $outputPath,
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($outputPath);

        $decoded = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(1, $decoded['schema_version']);
        self::assertSame('unsupervised_8', $decoded['case']);
        self::assertSame('unsupervised', $decoded['mode']);
        self::assertSame(8, $decoded['size']);
        self::assertSame([
            'population' => 6,
            'generations' => 2,
            'max_frames' => 6,
        ], $decoded['hyperparameters']);
        self::assertArrayHasKey('sequence_hash', $decoded);
        self::assertArrayHasKey('reproducible', $decoded);
        self::assertSame($decoded['reproducible'], $decoded['reproducibility']['match']);
        self::assertSame('double_run_identical_params', $decoded['reproducibility']['method']);
        self::assertArrayHasKey('stability', $decoded);
        self::assertArrayHasKey('diversity', $decoded);
        self::assertArrayHasKey('visual_perplexity', $decoded);

        $written = json_decode((string) file_get_contents($outputPath), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame($decoded['sequence_hash'], $written['sequence_hash']);
    }

    public function testItRunsSupervisedBenchmarkAndWritesJson(): void
    {
        $randomA = new MtRandomGenerator();
        $randomB = new MtRandomGenerator();
        $executor = new FlowProgramExecutor(
            new LambdaEvaluationService(new GameOfLifePrimitiveInvoker(), ['birth', 'kill', 'paint', 'next', 'mirror']),
            new GameOfLifeStepper(),
        );

        $unsupervised = new UnsupervisedTrainingService(
            $executor,
            new EvolutionEngine(
                new TournamentSelector($randomA),
                new AstCrossoverOperator($randomA),
                new AstMutationOperator($randomA),
                $randomA,
            ),
            $randomA,
        );

        $supervised = new SupervisedTrainingService(
            $executor,
            new EvolutionEngine(
                new TournamentSelector($randomB),
                new AstCrossoverOperator($randomB),
                new AstMutationOperator($randomB),
                $randomB,
            ),
            $randomB,
        );

        $command = new BenchmarkRunCommand(
            $supervised,
            $unsupervised,
            $this->createLogger(),
            $this->projectDir(),
        );
        $tester = new CommandTester($command);

        $outputPath = sys_get_temp_dir() . '/llm_game_of_life_tests/benchmarks/test-supervised-result.json';
        $status = $tester->execute([
            '--mode' => 'supervised',
            '--dataset' => 'block',
            '--population' => '6',
            '--generations' => '2',
            '--seed' => '42',
            '--max-frames' => '6',
            '--output' => $outputPath,
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($outputPath);

        $decoded = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('supervised_block', $decoded['case']);
        self::assertSame(1, $decoded['schema_version']);
        self::assertSame('supervised', $decoded['mode']);
        self::assertSame('block', $decoded['dataset']);
        self::assertSame([
            'population' => 6,
            'generations' => 2,
            'max_frames' => 6,
        ], $decoded['hyperparameters']);
        self::assertArrayHasKey('final_average_fitness', $decoded);
        self::assertArrayHasKey('sequence_hash', $decoded);
        self::assertTrue($decoded['reproducible']);
        self::assertTrue($decoded['reproducibility']['match']);
    }

    private function createLogger(): LifeRunLogger
    {
        $logPath = sys_get_temp_dir() . '/llm_game_of_life_tests/life_llm_runs.ndjson';

        return new LifeRunLogger(
            new WorkflowRunner(),
            new LogRunEventAction($logPath),
            new LogGenerationAction($logPath),
            new LogChunkAction($logPath),
            new LogBenchmarkAction($logPath),
        );
    }

    private function projectDir(): string
    {
        return \dirname(__DIR__, 2);
    }
}
