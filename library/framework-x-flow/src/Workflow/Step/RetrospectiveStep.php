<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;

/**
 * Deterministic retrospective: proposes workflow improvements without rewriting code.
 */
final class RetrospectiveStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('retrospective');

        $lessons = [];
        $suggestions = [];
        $slow = [];

        foreach ($ctx->steps as $step) {
            $duration = (float) ($step['durationMs'] ?? 0);
            if ($duration >= 50.0) {
                $slow[] = $step['name'];
                $lessons[] = sprintf('Step "%s" took %.1fms — consider caching or parallelizing.', $step['name'], $duration);
            }
            if (($step['status'] ?? '') === 'failed') {
                $lessons[] = sprintf('Step "%s" failed and should be investigated.', $step['name']);
            }
        }

        if ($ctx->failureCode === 'quality_check_failed') {
            $suggestions[] = [
                'target' => 'security-review',
                'reason' => 'The security check failed because no dependency audit result was supplied.',
                'proposal' => 'Add a dependency audit step before final review.',
            ];
        }

        if (!isset($ctx->input['files']) || $ctx->input['files'] === []) {
            $lessons[] = 'Missing input: files list was empty or absent.';
            $suggestions[] = [
                'target' => 'validate',
                'reason' => 'Runs without file lists produce weak analysis.',
                'proposal' => 'Reject empty files arrays earlier and document required inputs.',
            ];
        }

        foreach ($ctx->warnings as $warning) {
            $lessons[] = 'Warning observed: ' . $warning;
        }

        if ($suggestions === [] && $ctx->status === 'completed') {
            $suggestions[] = [
                'target' => 'quality-checks',
                'reason' => 'Successful runs still benefit from recording check durations in team memory.',
                'proposal' => 'Persist per-check baselines so future retrospectives can flag regressions.',
            ];
        }

        $summary = $ctx->status === 'completed'
            ? 'Change review completed. Encode checklist items that mattered into shared workflow memory.'
            : 'Change review failed. Treat the failure as a workflow gap, not only a one-off agent mistake.';

        $retrospective = [
            'summary' => $summary,
            'lessons' => array_values(array_unique($lessons)),
            'slowSteps' => $slow,
            'suggestedWorkflowChanges' => $suggestions,
            'note' => 'Proposals only — this step does not rewrite application code or workflow definitions.',
        ];

        $ctx->retrospective = $retrospective;
        $ctx->completeStep('retrospective', $t, $retrospective);
        $ctx->finish($ctx->status === 'failed' ? 'failed' : 'completed');

        return $ctx;
    }
}
