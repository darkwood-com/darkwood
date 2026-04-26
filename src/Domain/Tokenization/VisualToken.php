<?php

declare(strict_types=1);

namespace App\Domain\Tokenization;

final class VisualToken
{
    public const string FRAME_START = 'FRAME_START';
    public const string FRAME_END = 'FRAME_END';
    public const string NEXT_FRAME = 'NEXT_FRAME';
    public const string ALIVE = 'ALIVE';
    public const string DEAD = 'DEAD';
    public const string RED_COLOR = 'RED_COLOR';
    public const string BLUE_COLOR = 'BLUE_COLOR';
    public const string WHITE_COLOR = 'WHITE_COLOR';
    public const string BLACK_COLOR = 'BLACK_COLOR';

    private function __construct()
    {
    }
}
