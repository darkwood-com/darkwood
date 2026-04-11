<?php

declare(strict_types=1);

/**
 * Runnable without live Composio: demonstrates primitive variable + application.
 *
 * Run: php examples/lambda_tool.php
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Lambda\LambdaEvaluationService;
use App\Domain\Execution\LambdaExecutionContext;
use App\Domain\Primitive\PrimitiveInvocation;
use App\Domain\Primitive\PrimitiveInvokerInterface;

$invoker = new class implements PrimitiveInvokerInterface {
    public function invoke(PrimitiveInvocation $invocation, \App\Domain\Execution\LambdaExecutionContext $context): mixed
    {
        return [
            'primitive' => $invocation->primitiveName,
            'arguments' => $invocation->arguments,
            'user' => $context->composioUserId,
        ];
    }
};

$eval = new LambdaEvaluationService($invoker, ['send_email']);
$ctx = new LambdaExecutionContext('demo-user');
$result = $eval->evaluate('λsend_email.λpayload.send_email(payload)', $ctx, [[
    'to' => 'team@example.com',
    'subject' => 'Hello',
]]);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
