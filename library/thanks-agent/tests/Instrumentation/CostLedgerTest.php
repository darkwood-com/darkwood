<?php

declare(strict_types=1);

namespace App\Tests\Instrumentation;

use App\Instrumentation\CostLedger;
use App\Model\AgentId;
use App\Model\AgentResult;
use Flow\Instrumentation\CostLedger as FlowCostLedger;
use Flow\Budget\SupervisionBudget;
use PHPUnit\Framework\TestCase;

final class CostLedgerTest extends TestCase
{
    public function testThanksAddsCostWithoutValue(): void
    {
        $ledger = new CostLedger();
        $ledger->recordResult(new AgentResult(
            agentId: AgentId::Thanks,
            runId: 't',
            output: 'Thanks.',
            value: 0,
            latencyMs: 10,
            messages: 28,
            tokens: 2,
            cost: 0.008,
            didWork: false,
        ));

        $snapshot = $ledger->snapshot();
        self::assertSame(0, $snapshot['byAgent'][AgentId::Thanks->value]['value']);
        self::assertGreaterThan(0.0, $snapshot['byAgent'][AgentId::Thanks->value]['cost']);
    }

    public function testPromotedFlowLedgerIsAvailable(): void
    {
        $ledger = new FlowCostLedger();
        $ledger->recordComplete('GithubAgent', 100);
        $ledger->recordError('CostAgent', 32);
        $snapshot = $ledger->snapshot();

        self::assertSame(1, $snapshot['totals']['successes']);
        self::assertSame(1, $snapshot['totals']['errors']);
        self::assertNotNull($snapshot['totals']['costPerSuccessfulOutcome']);
    }

    public function testSupervisionBudgetCapsAtFive(): void
    {
        $budget = new SupervisionBudget(5);
        for ($i = 0; $i < 5; ++$i) {
            self::assertTrue($budget->tryAcquire());
        }
        self::assertFalse($budget->tryAcquire());
    }
}
