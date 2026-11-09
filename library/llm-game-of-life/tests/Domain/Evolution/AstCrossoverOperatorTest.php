<?php

declare(strict_types=1);

namespace App\Tests\Domain\Evolution;

use App\Domain\Evolution\AstCrossoverOperator;
use PHPUnit\Framework\TestCase;

final class AstCrossoverOperatorTest extends TestCase
{
    public function testItBuildsChildrenFromBothParents(): void
    {
        $left = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'birth', 'x' => 1, 'y' => 1],
                ['type' => 'next'],
            ],
        ];
        $right = [
            'type' => 'sequence',
            'nodes' => [
                ['type' => 'kill', 'x' => 2, 'y' => 2],
                ['type' => 'paint', 'x' => 2, 'y' => 2, 'color' => '#ffffff'],
                ['type' => 'next'],
            ],
        ];

        $operator = new AstCrossoverOperator(new FakeRandomGenerator([1, 1]));
        [$childA, $childB] = $operator->crossover($left, $right);

        self::assertSame('sequence', $childA['type']);
        self::assertSame('sequence', $childB['type']);
        self::assertNotEmpty($childA['nodes']);
        self::assertNotEmpty($childB['nodes']);
    }
}
