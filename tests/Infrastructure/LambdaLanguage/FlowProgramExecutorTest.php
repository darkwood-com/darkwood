<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\LambdaLanguage;

use App\Domain\CellularAutomaton\GameOfLifeStepper;
use App\Domain\CellularAutomaton\Grid;
use App\Domain\LambdaLanguage\LambdaEvaluationService;
use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramValidator;
use App\Infrastructure\LambdaLanguage\FlowProgramExecutor;
use App\Infrastructure\LambdaLanguage\GameOfLifePrimitiveInvoker;
use PHPUnit\Framework\TestCase;

final class FlowProgramExecutorTest extends TestCase
{
    public function testItExecutesProgramThroughFlowPrimitives(): void
    {
        $program = Program::fromArray([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 1],
                ['type' => 'birth', 'x' => 1, 'y' => 2],
                ['type' => 'birth', 'x' => 2, 'y' => 1],
                ['type' => 'birth', 'x' => 2, 'y' => 2],
                ['type' => 'next'],
            ],
        ], new ProgramValidator(gridSize: 5));

        $executor = $this->buildExecutor();
        $frames = $executor->execute($program, Grid::empty(5), maxFrames: 10);

        self::assertCount(2, $frames);
        self::assertFalse($frames[0]->grid()->isAlive(1, 1));
        self::assertTrue($frames[1]->grid()->isAlive(1, 1));
    }

    public function testComposeAndRepeatAreExecuted(): void
    {
        $program = Program::fromArray([
            'type' => 'sequence',
            'nodes' => [[
                'type' => 'compose',
                'first' => [
                    'type' => 'sequence',
                    'nodes' => [
                        ['type' => 'birth', 'x' => 2, 'y' => 2],
                        ['type' => 'birth', 'x' => 2, 'y' => 3],
                        ['type' => 'birth', 'x' => 3, 'y' => 2],
                        ['type' => 'birth', 'x' => 3, 'y' => 3],
                    ],
                ],
                'second' => [
                    'type' => 'repeat',
                    'n' => 2,
                    'node' => ['type' => 'next'],
                ],
            ]],
        ], new ProgramValidator(gridSize: 5));

        $executor = $this->buildExecutor();
        $frames = $executor->execute($program, Grid::empty(5), maxFrames: 10);

        self::assertCount(3, $frames);
        self::assertTrue($frames[1]->grid()->isAlive(2, 2));
        self::assertTrue($frames[2]->grid()->isAlive(2, 2));
    }

    private function buildExecutor(): FlowProgramExecutor
    {
        $evaluation = new LambdaEvaluationService(
            new GameOfLifePrimitiveInvoker(),
            ['birth', 'kill', 'paint', 'next'],
        );

        return new FlowProgramExecutor($evaluation, new GameOfLifeStepper());
    }
}
