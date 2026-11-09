<?php

declare(strict_types=1);

/**
 * Runnable without Symfony: pure lambda (no primitives).
 *
 * Run: php examples/simple_lambda.php
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application\Lambda\LambdaEvaluationService;
use App\Domain\Execution\LambdaExecutionContext;

$eval = new LambdaEvaluationService(null, []);
$result = $eval->evaluate('λx.x', new LambdaExecutionContext(), [5]);

echo "Result: " . json_encode($result, JSON_THROW_ON_ERROR) . "\n";
