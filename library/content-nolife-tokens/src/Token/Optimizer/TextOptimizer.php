<?php

declare(strict_types=1);

namespace App\Token\Optimizer;

use App\Token\OptimizeResult;

/**
 * Light whitespace normalization only — no arbitrary truncation.
 */
final class TextOptimizer
{
    public function optimize(string $content): OptimizeResult
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $content = preg_replace("/[ \t]+$/m", '', $content) ?? $content;
        $content = preg_replace("/\n{3,}/", "\n\n", $content) ?? $content;

        return new OptimizeResult(trim($content));
    }
}
