<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;
use Flow\DriverInterface;

final class RunTestsStep
{
    public function __construct(
        private readonly DriverInterface $driver,
        private readonly float $delaySeconds = 0.04,
    ) {
    }

    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('tests');
        $this->driver->delay($this->delaySeconds);

        if (($ctx->input['fail'] ?? null) === 'async') {
            throw new \RuntimeException('Controlled asynchronous failure during tests check (fail=async)');
        }

        $output = [
            'status' => 'passed',
            'message' => 'Deterministic tests check passed.',
            'assertions' => 12,
        ];
        $ctx->result['tests'] = $output;
        $ctx->completeStep('tests', $t, $output);

        return $ctx;
    }
}
