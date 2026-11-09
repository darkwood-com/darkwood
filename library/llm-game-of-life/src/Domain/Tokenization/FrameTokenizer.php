<?php

declare(strict_types=1);

namespace App\Domain\Tokenization;

use App\Domain\CellularAutomaton\Frame;

final class FrameTokenizer
{
    /**
     * @param Frame|array{cells:list<list<int>>} $frame
     *
     * @return list<string>
     */
    public function tokenizeFrame(Frame|array $frame): array
    {
        $cells = $frame instanceof Frame ? $frame->grid()->toBinaryMatrix() : $this->readCells($frame);
        $tokens = [VisualToken::FRAME_START];

        foreach ($cells as $row) {
            foreach ($row as $cell) {
                $tokens[] = $cell === 1 ? VisualToken::ALIVE : VisualToken::DEAD;
            }
        }

        $tokens[] = VisualToken::FRAME_END;

        return $tokens;
    }

    /**
     * @param list<Frame|array{cells:list<list<int>>}> $frames
     *
     * @return list<string>
     */
    public function tokenizeFrames(array $frames): array
    {
        $tokens = [];

        foreach ($frames as $index => $frame) {
            if ($index > 0) {
                $tokens[] = VisualToken::NEXT_FRAME;
            }

            array_push($tokens, ...$this->tokenizeFrame($frame));
        }

        return $tokens;
    }

    /**
     * @param array<string,mixed> $frame
     *
     * @return list<list<int>>
     */
    private function readCells(array $frame): array
    {
        $cells = $frame['cells'] ?? null;
        if (!\is_array($cells)) {
            throw new \InvalidArgumentException('Frame payload must contain cells.');
        }

        $normalized = [];
        foreach ($cells as $row) {
            if (!\is_array($row)) {
                throw new \InvalidArgumentException('Frame cells must be a matrix.');
            }

            $normalizedRow = [];
            foreach ($row as $cell) {
                if ($cell !== 0 && $cell !== 1) {
                    throw new \InvalidArgumentException('Frame cells must contain only 0 or 1.');
                }

                $normalizedRow[] = $cell;
            }

            $normalized[] = $normalizedRow;
        }

        return $normalized;
    }
}
