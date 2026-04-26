<?php

declare(strict_types=1);

namespace App\Tests\Domain\Tokenization;

use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramValidator;
use App\Domain\Tokenization\ProgramTokenizer;
use App\Domain\Tokenization\VisualToken;
use PHPUnit\Framework\TestCase;

final class ProgramTokenizerTest extends TestCase
{
    public function testItTokenizesPrimitiveInstructions(): void
    {
        $program = Program::fromArray([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 2],
                ['type' => 'kill', 'x' => 2, 'y' => 3],
                ['type' => 'paint', 'x' => 0, 'y' => 1, 'color' => '#ff0000'],
                ['type' => 'paint', 'x' => 1, 'y' => 0, 'color' => '#0000ff'],
                ['type' => 'mirror', 'axis' => 'vertical'],
                ['type' => 'next'],
            ],
        ], new ProgramValidator(gridSize: 4));

        self::assertSame([
            'BIRTH_1_2',
            'KILL_2_3',
            'PAINT_0_1_' . VisualToken::RED_COLOR,
            'PAINT_1_0_' . VisualToken::BLUE_COLOR,
            'MIRROR_VERTICAL',
            VisualToken::NEXT_FRAME,
        ], (new ProgramTokenizer())->tokenizeProgram($program));
    }

    public function testItFlattensRepeatAndComposeInExecutionOrder(): void
    {
        $program = Program::fromArray([
            'type' => 'sequence',
            'nodes' => [
                [
                    'type' => 'repeat',
                    'n' => 2,
                    'node' => ['type' => 'next'],
                ],
                [
                    'type' => 'compose',
                    'first' => ['type' => 'birth', 'x' => 0, 'y' => 0],
                    'second' => ['type' => 'kill', 'x' => 1, 'y' => 1],
                ],
            ],
        ], new ProgramValidator(gridSize: 2));

        self::assertSame([
            VisualToken::NEXT_FRAME,
            VisualToken::NEXT_FRAME,
            'BIRTH_0_0',
            'KILL_1_1',
        ], (new ProgramTokenizer())->tokenizeProgram($program));
    }
}
