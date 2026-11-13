<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;

final class AnalyseChangeStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('analyse');

        /** @var list<string> $files */
        $files = array_values(array_filter($ctx->input['files'] ?? [], 'is_string'));
        $areas = [];
        foreach ($files as $file) {
            if (str_contains($file, 'Http/') || str_contains($file, 'Handler')) {
                $areas['http'] = true;
            }
            if (str_contains($file, 'Workflow/') || str_contains($file, 'Flow/')) {
                $areas['orchestration'] = true;
            }
            if (str_contains($file, 'docs/') || str_ends_with($file, '.md')) {
                $areas['documentation'] = true;
            }
        }

        $analysis = [
            'areas' => array_keys($areas) ?: ['general'],
            'estimatedComplexity' => count($files) > 5 ? 'high' : (count($files) > 2 ? 'medium' : 'low'),
            'risk' => $ctx->input['risk'] ?? 'medium',
        ];

        $ctx->result['analysis'] = $analysis;
        $ctx->completeStep('analyse', $t, $analysis);

        return $ctx;
    }
}
