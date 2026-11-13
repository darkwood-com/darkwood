<?php

declare(strict_types=1);

namespace App\Tests;

use App\Application\Lambda\LambdaEvaluationService;
use App\Domain\Execution\LambdaExecutionContext;
use App\Domain\Primitive\PrimitiveInvocation;
use App\Domain\Primitive\PrimitiveInvokerInterface;
use PHPUnit\Framework\TestCase;

final class LambdaEvaluationWithPrimitivesTest extends TestCase
{
    public function testPrimitiveBindingCanBeInvokedWithoutPayload(): void
    {
        $invoker = new class implements PrimitiveInvokerInterface {
            public mixed $last = null;

            public function invoke(PrimitiveInvocation $invocation, \App\Domain\Execution\LambdaExecutionContext $context): mixed
            {
                $this->last = $invocation;

                return ['ok' => true, 'name' => $invocation->primitiveName];
            }
        };

        $eval = new LambdaEvaluationService($invoker, ['send_email']);
        $ctx = new LambdaExecutionContext('u1');
        $out = $eval->evaluate('λsend_email.send_email', $ctx);

        self::assertSame(['ok' => true, 'name' => 'send_email'], $out);
        self::assertSame('send_email', $invoker->last?->primitiveName);
    }

    public function testPrimitiveBindingCanReceiveAppliedPayload(): void
    {
        $invoker = new class implements PrimitiveInvokerInterface {
            public ?PrimitiveInvocation $last = null;

            public function invoke(PrimitiveInvocation $invocation, \App\Domain\Execution\LambdaExecutionContext $context): mixed
            {
                $this->last = $invocation;

                return $invocation->arguments;
            }
        };

        $eval = new LambdaEvaluationService($invoker, ['send_email']);
        $ctx = new LambdaExecutionContext('u1');
        $out = $eval->evaluate('λsend_email.λpayload.send_email(payload)', $ctx, [7]);

        self::assertSame(['value' => 7], $out);
        self::assertSame(7, $invoker->last?->arguments['value'] ?? null);
    }
}
