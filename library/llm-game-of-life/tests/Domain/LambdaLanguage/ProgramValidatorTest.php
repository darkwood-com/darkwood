<?php

declare(strict_types=1);

namespace App\Tests\Domain\LambdaLanguage;

use App\Domain\LambdaLanguage\Program;
use App\Domain\LambdaLanguage\ProgramValidationException;
use App\Domain\LambdaLanguage\ProgramValidator;
use PHPUnit\Framework\TestCase;

final class ProgramValidatorTest extends TestCase
{
    public function testItAcceptsAV1ProgramUsingCorePrimitives(): void
    {
        $validator = new ProgramValidator(gridSize: 16);
        $program = Program::fromArray([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 2],
                ['type' => 'paint', 'x' => 1, 'y' => 2, 'color' => '#ffffff'],
                ['type' => 'mirror', 'axis' => 'vertical'],
                [
                    'type' => 'compose',
                    'first' => ['type' => 'repeat', 'n' => 2, 'node' => ['type' => 'next']],
                    'second' => ['type' => 'kill', 'x' => 3, 'y' => 3],
                ],
            ],
        ], $validator);

        self::assertSame('sequence', $program->toArray()['type']);
    }

    public function testItRejectsUnsupportedNodeType(): void
    {
        $validator = new ProgramValidator(gridSize: 16);

        $this->expectException(ProgramValidationException::class);

        $validator->validate([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'teleport', 'x' => 1, 'y' => 1],
            ],
        ]);
    }

    public function testItRejectsOutOfBoundsCoordinates(): void
    {
        $validator = new ProgramValidator(gridSize: 16);

        $this->expectException(ProgramValidationException::class);

        $validator->validate([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 16, 'y' => 0],
            ],
        ]);
    }

    public function testItRejectsInvalidPaintColor(): void
    {
        $validator = new ProgramValidator(gridSize: 16);

        $this->expectException(ProgramValidationException::class);

        $validator->validate([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'paint', 'x' => 1, 'y' => 1, 'color' => 'white'],
            ],
        ]);
    }

    public function testItRejectsInvalidMirrorAxis(): void
    {
        $validator = new ProgramValidator(gridSize: 16);

        $this->expectException(ProgramValidationException::class);

        $validator->validate([
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'mirror', 'axis' => 'diagonal'],
            ],
        ]);
    }

    public function testItRejectsTooDeepProgramTree(): void
    {
        $validator = new ProgramValidator(gridSize: 16, maxDepth: 3);

        $this->expectException(ProgramValidationException::class);

        $validator->validate([
            'type' => 'sequence',
            'nodes' => [[
                'type' => 'repeat',
                'n' => 1,
                'node' => [
                    'type' => 'repeat',
                    'n' => 1,
                    'node' => [
                        'type' => 'repeat',
                        'n' => 1,
                        'node' => ['type' => 'next'],
                    ],
                ],
            ]],
        ]);
    }
}
