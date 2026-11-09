<?php

declare(strict_types=1);

namespace App\Tests\Domain\Tokenization;

use App\Domain\CellularAutomaton\Frame;
use App\Domain\CellularAutomaton\Grid;
use App\Domain\Tokenization\FrameTokenizer;
use App\Domain\Tokenization\VisualToken;
use PHPUnit\Framework\TestCase;

final class FrameTokenizerTest extends TestCase
{
    public function testItTokenizesFrameCellsInRowMajorOrder(): void
    {
        $frame = new Frame(0, Grid::fromAliveCoordinates(2, [
            ['x' => 0, 'y' => 0],
            ['x' => 1, 'y' => 1],
        ]));

        self::assertSame([
            VisualToken::FRAME_START,
            VisualToken::ALIVE,
            VisualToken::DEAD,
            VisualToken::DEAD,
            VisualToken::ALIVE,
            VisualToken::FRAME_END,
        ], (new FrameTokenizer())->tokenizeFrame($frame));
    }

    public function testItSeparatesMultipleFramesWithNextFrameToken(): void
    {
        $first = ['cells' => [[0, 1]]];
        $second = ['cells' => [[1, 0]]];

        self::assertSame([
            VisualToken::FRAME_START,
            VisualToken::DEAD,
            VisualToken::ALIVE,
            VisualToken::FRAME_END,
            VisualToken::NEXT_FRAME,
            VisualToken::FRAME_START,
            VisualToken::ALIVE,
            VisualToken::DEAD,
            VisualToken::FRAME_END,
        ], (new FrameTokenizer())->tokenizeFrames([$first, $second]));
    }
}
