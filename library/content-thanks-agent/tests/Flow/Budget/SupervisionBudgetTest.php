<?php

declare(strict_types=1);

namespace App\Tests\Flow\Budget;

use App\Flow\Budget\SupervisionBudget;
use PHPUnit\Framework\TestCase;

final class SupervisionBudgetTest extends TestCase
{
    public function testAcquireReleaseAndClamp(): void
    {
        $budget = new SupervisionBudget(5);

        self::assertTrue($budget->tryAcquire());
        self::assertTrue($budget->tryAcquire());
        self::assertSame(2, $budget->inFlight());
        self::assertSame(3, $budget->remaining());

        $budget->release();
        self::assertSame(1, $budget->inFlight());

        self::assertSame(5, $budget->clampConcurrency(99));
        self::assertSame(1, $budget->clampConcurrency(0));
    }

    public function testRejectsWhenFull(): void
    {
        $budget = new SupervisionBudget(2);
        self::assertTrue($budget->tryAcquire());
        self::assertTrue($budget->tryAcquire());
        self::assertFalse($budget->tryAcquire());
    }
}
