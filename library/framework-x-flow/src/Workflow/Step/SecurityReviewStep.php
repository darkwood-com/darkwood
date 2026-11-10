<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;
use Flow\DriverInterface;

final class SecurityReviewStep
{
    public function __construct(
        private readonly DriverInterface $driver,
        private readonly float $delaySeconds = 0.06,
    ) {
    }

    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('security-review');
        $this->driver->delay($this->delaySeconds);

        if (($ctx->input['fail'] ?? null) === 'security') {
            $output = [
                'status' => 'failed',
                'message' => 'Security check failed: no dependency audit result was supplied.',
            ];
            $ctx->result['security'] = $output;
            $ctx->fail('quality_check_failed', $output['message']);
            $ctx->warnings[] = 'Quality check failed: security-review';
            $ctx->completeStep('security-review', $t, $output, 'failed');

            return $ctx;
        }

        $output = [
            'status' => 'passed',
            'message' => 'Deterministic security review passed.',
            'findings' => 0,
        ];
        $ctx->result['security'] = $output;
        $ctx->completeStep('security-review', $t, $output);

        return $ctx;
    }
}
