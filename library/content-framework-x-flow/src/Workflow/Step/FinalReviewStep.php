<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;

final class FinalReviewStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('final-review');

        $failedSteps = array_values(array_filter(
            $ctx->steps,
            static fn (array $step): bool => ($step['status'] ?? '') === 'failed'
        ));

        $decision = $ctx->status === 'failed' || $failedSteps !== []
            ? 'request_changes'
            : 'approve';

        $output = [
            'decision' => $decision,
            'failedStepCount' => count($failedSteps),
            'risk' => $ctx->input['risk'] ?? 'medium',
        ];
        $ctx->result['finalReview'] = $output;
        $ctx->completeStep('final-review', $t, $output);

        return $ctx;
    }
}
