<?php

declare(strict_types=1);

namespace App\Tests\Domain\Evolution;

use App\Domain\Evolution\Individual;
use App\Domain\Evolution\Population;
use App\Domain\Evolution\TournamentSelector;
use PHPUnit\Framework\TestCase;

final class TournamentSelectorTest extends TestCase
{
    public function testItSelectsBestWithinTournamentDraws(): void
    {
        $population = new Population([
            new Individual(['type' => 'sequence', 'nodes' => [['type' => 'next']]], 0.10),
            new Individual(['type' => 'sequence', 'nodes' => [['type' => 'next']]], 0.95),
            new Individual(['type' => 'sequence', 'nodes' => [['type' => 'next']]], 0.50),
        ]);

        $selector = new TournamentSelector(new FakeRandomGenerator([0, 2, 1]));
        $picked = $selector->select($population, 3);

        self::assertSame(0.95, $picked->fitness);
    }
}
