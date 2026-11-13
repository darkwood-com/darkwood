<?php

declare(strict_types=1);

namespace App\Workflow\Step;

use App\Workflow\ChangeReviewContext;
use InvalidArgumentException;

final class ValidateChangeRequestStep
{
    public function __invoke(ChangeReviewContext $ctx): ChangeReviewContext
    {
        $t = $ctx->beginStep('validate');

        $changeId = $ctx->input['changeId'] ?? null;
        $title = $ctx->input['title'] ?? null;
        $files = $ctx->input['files'] ?? null;
        $risk = $ctx->input['risk'] ?? null;

        if (!is_string($changeId) || $changeId === '') {
            throw new InvalidArgumentException('Missing required field: changeId');
        }
        if (!is_string($title) || $title === '') {
            throw new InvalidArgumentException('Missing required field: title');
        }
        if (!is_array($files) || $files === []) {
            throw new InvalidArgumentException('Missing required field: files (non-empty array)');
        }
        foreach ($files as $file) {
            if (!is_string($file) || $file === '') {
                throw new InvalidArgumentException('Each file path must be a non-empty string');
            }
        }
        if (!is_string($risk) || !in_array($risk, ['low', 'medium', 'high'], true)) {
            throw new InvalidArgumentException('Field risk must be one of: low, medium, high');
        }

        if (($ctx->input['fail'] ?? null) === 'exception') {
            throw new InvalidArgumentException('Controlled synchronous exception (fail=exception)');
        }

        $ctx->completeStep('validate', $t, [
            'changeId' => $changeId,
            'title' => $title,
            'fileCount' => count($files),
            'risk' => $risk,
        ]);

        return $ctx;
    }
}
