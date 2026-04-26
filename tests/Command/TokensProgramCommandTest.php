<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\TokensProgramCommand;
use App\Domain\Tokenization\ProgramTokenizer;
use App\Domain\Tokenization\VisualToken;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class TokensProgramCommandTest extends TestCase
{
    public function testItTokenizesProgramAstJson(): void
    {
        $tester = new CommandTester(new TokensProgramCommand(new ProgramTokenizer()));
        $status = $tester->execute([
            '--size' => '4',
            '--ast' => json_encode([
                'type' => 'sequence',
                'nodes' => [
                    ['type' => 'birth', 'x' => 1, 'y' => 2],
                    ['type' => 'paint', 'x' => 0, 'y' => 1, 'color' => '#0000ff'],
                    ['type' => 'next'],
                ],
            ], JSON_THROW_ON_ERROR),
        ]);

        self::assertSame(Command::SUCCESS, $status);
        self::assertSame([
            'BIRTH_1_2',
            'PAINT_0_1_' . VisualToken::BLUE_COLOR,
            VisualToken::NEXT_FRAME,
        ], json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR));
    }
}
