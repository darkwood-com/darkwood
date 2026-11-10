<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;

final class GenerateDocumentationStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('documentation');

        $title = (string) ($ctx->input['title'] ?? 'untitled');
        $files = $ctx->input['files'] ?? [];
        $summary = sprintf(
            'Documentation summary for "%s" covering %d file(s). Areas: %s.',
            $title,
            is_array($files) ? count($files) : 0,
            implode(', ', $ctx->result['analysis']['areas'] ?? ['general'])
        );

        $output = [
            'summary' => $summary,
            'sections' => ['overview', 'changes', 'risks', 'verification'],
        ];
        $ctx->result['documentation'] = $output;
        $ctx->completeStep('documentation', $t, $output);

        return $ctx;
    }
}
