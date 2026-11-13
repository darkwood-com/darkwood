<?php

declare(strict_types=1);

namespace App\Tests\Flow\Instrumentation;

use App\Flow\Instrumentation\CostLedger;
use PHPUnit\Framework\TestCase;

final class CostLedgerTest extends TestCase
{
    public function testMetersSuccessAndErrorTerminals(): void
    {
        $ledger = new CostLedger();
        $ledger->recordComplete('GithubAgent', 100);
        $ledger->recordError('CostAgent', 32);
        $ledger->recordPush();
        $ledger->recordPull();

        $snapshot = $ledger->snapshot();

        self::assertSame(1, $snapshot['events']['complete']);
        self::assertSame(1, $snapshot['events']['error']);
        self::assertSame(1, $snapshot['totals']['successes']);
        self::assertSame(1, $snapshot['totals']['errors']);
        self::assertGreaterThan(0.0, $snapshot['totals']['totalCost']);
        self::assertNotNull($snapshot['totals']['costPerSuccessfulOutcome']);
    }

    public function testResetClearsState(): void
    {
        $ledger = new CostLedger();
        $ledger->recordComplete('a', 10);
        $ledger->reset();

        $snapshot = $ledger->snapshot();
        self::assertSame(0, $snapshot['events']['complete']);
        self::assertSame(0.0, $snapshot['totals']['totalCost']);
    }
}
