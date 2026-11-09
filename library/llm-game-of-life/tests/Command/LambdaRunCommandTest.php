<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\LambdaRunCommand;
use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramExecutorInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class LambdaRunCommandTest extends TestCase
{
    public function testItStreamsFramesAsNdjson(): void
    {
        $executor = new class implements ProgramExecutorInterface {
            public function execute(Program $program, Grid $initialGrid, int $maxFrames = 120): array
            {
                $stateful = $initialGrid
                    ->withCellState(0, 0, \App\Domain\CellularAutomaton\CellState::Alive)
                    ->withCellState(1, 0, \App\Domain\CellularAutomaton\CellState::Alive)
                    ->withCellState(0, 1, \App\Domain\CellularAutomaton\CellState::Alive)
                    ->withCellState(1, 1, \App\Domain\CellularAutomaton\CellState::Alive);

                return [
                    new Frame(0, $initialGrid),
                    new Frame(1, (new GameOfLifeStepper())->next($stateful)),
                ];
            }
        };

        $command = new LambdaRunCommand($executor);
        $tester = new CommandTester($command);

        $status = $tester->execute([
            '--size' => '8',
            '--max-frames' => '10',
            '--ast' => '{"type":"sequence","nodes":[{"type":"next"}]}',
        ]);

        self::assertSame(Command::SUCCESS, $status);

        $lines = array_values(array_filter(array_map('trim', explode("\n", $tester->getDisplay()))));
        self::assertCount(2, $lines);

        $first = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        $second = json_decode($lines[1], true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(0, $first['frame']);
        self::assertSame(1, $second['frame']);
        self::assertSame(8, $first['size']);
    }

    public function testItFailsWhenAstIsMissing(): void
    {
        $executor = new class implements ProgramExecutorInterface {
            public function execute(Program $program, Grid $initialGrid, int $maxFrames = 120): array
            {
                return [];
            }
        };

        $command = new LambdaRunCommand($executor);
        $tester = new CommandTester($command);

        $status = $tester->execute([
            '--size' => '8',
        ]);

        self::assertSame(Command::FAILURE, $status);
    }

    public function testItWritesNdjsonWhenOutputIsProvided(): void
    {
        $executor = new class implements ProgramExecutorInterface {
            public function execute(Program $program, Grid $initialGrid, int $maxFrames = 120): array
            {
                return [
                    new Frame(0, $initialGrid),
                    new Frame(1, $initialGrid->withCellState(0, 0, \App\Domain\CellularAutomaton\CellState::Alive)),
                ];
            }
        };

        $command = new LambdaRunCommand($executor);
        $tester = new CommandTester($command);

        $baseDir = sys_get_temp_dir() . '/llm_game_of_life_tests';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0777, true);
        }
        $outputPath = $baseDir . '/lambda-run.ndjson';
        @unlink($outputPath);

        $status = $tester->execute([
            '--size' => '8',
            '--max-frames' => '10',
            '--ast' => '{"type":"sequence","nodes":[{"type":"next"}]}',
            '--output' => $outputPath,
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertFileExists($outputPath);
    }
}
