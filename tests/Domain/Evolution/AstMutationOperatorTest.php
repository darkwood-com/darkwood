<?php

declare(strict_types=1);

namespace App\Tests\Domain\Evolution;

use App\Domain\Evolution\AstMutationOperator;
use PHPUnit\Framework\TestCase;

final class AstMutationOperatorTest extends TestCase
{
    public function testItMutatesCoordinatesAndKeepsNextInstruction(): void
    {
        $program = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 0, 'y' => 0],
                ['type' => 'next'],
            ],
        ];

        $mutation = new AstMutationOperator(new FakeRandomGenerator([0, 0, 3, 4]));
        $mutated = $mutation->mutate($program, 8);

        self::assertSame(3, $mutated['nodes'][0]['x']);
        self::assertSame(4, $mutated['nodes'][0]['y']);
        self::assertContains('next', array_column($mutated['nodes'], 'type'));
    }

    public function testItCanAppendMirrorNodes(): void
    {
        $program = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'next'],
            ],
        ];

        $mutation = new AstMutationOperator(new FakeRandomGenerator([2, 3, 0, 0, 1]));
        $mutated = $mutation->mutate($program, 8);

        self::assertSame('mirror', $mutated['nodes'][1]['type']);
        self::assertSame('vertical', $mutated['nodes'][1]['axis']);
        self::assertContains('next', array_column($mutated['nodes'], 'type'));
    }
}
