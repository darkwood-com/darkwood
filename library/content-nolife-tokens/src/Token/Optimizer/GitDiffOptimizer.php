<?php

declare(strict_types=1);

namespace App\Token\Optimizer;

use App\Token\OptimizeResult;

/**
 * Keep file headers and changed lines; store collapsed unchanged context as one coarse ref.
 */
final class GitDiffOptimizer
{
    private const MAX_CONTEXT_RUN = 2;

    public function optimize(string $content): OptimizeResult
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $out = [];
        $contextRun = 0;
        /** @var list<string> $allOmitted */
        $allOmitted = [];

        foreach ($lines as $line) {
            if ($this->isNoiseMeta($line)) {
                continue;
            }

            if ($line !== '' && $line[0] === ' ' && !str_starts_with($line, '+++') && !str_starts_with($line, '---')) {
                ++$contextRun;
                if ($contextRun > self::MAX_CONTEXT_RUN) {
                    $allOmitted[] = $line;
                    if ($contextRun === self::MAX_CONTEXT_RUN + 1) {
                        $out[] = ' …';
                    }
                    continue;
                }
                $out[] = $line;
                continue;
            }

            $contextRun = 0;
            $out[] = $line;
        }

        $compact = [];
        $blank = false;
        foreach ($out as $line) {
            if (trim($line) === '') {
                if ($blank) {
                    continue;
                }
                $blank = true;
                $compact[] = '';
                continue;
            }
            $blank = false;
            $compact[] = $line;
        }

        $visible = rtrim(implode("\n", $compact));
        $omissions = [];
        if ($allOmitted !== []) {
            $placeholder = '{{REF_0}}';
            $visible .= "\n\nUnchanged diff context omitted.\n" . $placeholder;
            $omissions[] = [
                'reason' => 'collapsed unchanged diff context',
                'content' => implode("\n", $allOmitted),
                'marker_placeholder' => $placeholder,
            ];
        }

        return new OptimizeResult($visible, $omissions);
    }

    private function isNoiseMeta(string $line): bool
    {
        if (preg_match('/^index [0-9a-f]+\.\.[0-9a-f]+( \d+)?$/i', $line) === 1) {
            return true;
        }
        if (preg_match('/^(old|new) mode \d+$/', $line) === 1) {
            return true;
        }
        if (preg_match('/^similarity index \d+%$/', $line) === 1) {
            return true;
        }

        return false;
    }
}
