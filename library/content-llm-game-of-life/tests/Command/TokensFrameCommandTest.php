<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\TokensFrameCommand;
use App\Domain\Tokenization\FrameTokenizer;
use App\Domain\Tokenization\VisualToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class TokensFrameCommandTest extends TestCase
{
    public function testItTokenizesFrameJsonFile(): void
    {
        $path = sys_get_temp_dir() . '/llm_game_of_life_tests/frame.json';
        if (!is_dir(\dirname($path))) {
            mkdir(\dirname($path), 0777, true);
        }

        file_put_contents($path, json_encode([
            'frame' => 0,
            'size' => 2,
            'cells' => [[1, 0], [0, 1]],
        ], JSON_THROW_ON_ERROR));

        $tester = new CommandTester(new TokensFrameCommand(new FrameTokenizer()));
        $status = $tester->execute(['--input' => $path]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertSame([
            VisualToken::FRAME_START,
            VisualToken::ALIVE,
            VisualToken::DEAD,
            VisualToken::DEAD,
            VisualToken::ALIVE,
            VisualToken::FRAME_END,
        ], json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR));
    }
}
