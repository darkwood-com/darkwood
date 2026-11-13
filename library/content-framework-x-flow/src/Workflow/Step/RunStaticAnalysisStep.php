<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;
use Flow\DriverInterface;

final class RunStaticAnalysisStep
{
    public function __construct(
        private readonly DriverInterface $driver,
        private readonly float $delaySeconds = 0.05,
    ) {
    }

    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('static-analysis');
        $this->driver->delay($this->delaySeconds);

        $output = [
            'status' => 'passed',
            'message' => 'Deterministic static analysis check passed.',
            'issues' => 0,
        ];
        $ctx->result['staticAnalysis'] = $output;
        $ctx->completeStep('static-analysis', $t, $output);

        return $ctx;
    }
}
