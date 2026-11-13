<?php

declare(strict_types=1);

namespace App\Token\Optimizer;

use App\Token\OptimizeResult;

/**
 * Collapse consecutive identical log lines; keep error/warning lines verbatim.
 * Emit omissions so collapsed material can be retrieved via #ref markers.
 */
final class LogOptimizer
{
    public function optimize(string $content, string $granularity = 'coarse'): OptimizeResult
    {
        $lines = preg_split('/\R/', $content) ?: [];
        $out = [];
        $prev = null;
        $count = 0;
        /** @var list<array{reason: string, content: string}> $fineOmissions */
        $fineOmissions = [];
        /** @var list<string> $coarseParts */
        $coarseParts = [];

        $flush = function () use (&$out, &$prev, &$count, &$fineOmissions, &$coarseParts, $granularity): void {
            if ($prev === null) {
                return;
            }

            if ($count > 1) {
                $expanded = implode("\n", array_fill(0, $count, $prev));
                if ($granularity === 'fine') {
                    $placeholder = '{{REF_' . count($fineOmissions) . '}}';
                    $out[] = sprintf('%s ×%d %s', $prev, $count, $placeholder);
                    $fineOmissions[] = [
                        'reason' => 'collapsed repeated log lines',
                        'content' => $expanded,
                        'marker_placeholder' => $placeholder,
                    ];
                } else {
                    $out[] = sprintf('%s ×%d', $prev, $count);
                    $coarseParts[] = $expanded;
                }
            } else {
                $out[] = $prev;
            }

            $prev = null;
            $count = 0;
        };

        foreach ($lines as $line) {
            if ($line === '' && $prev === null) {
                continue;
            }

            if ($this->isHighSignal($line)) {
                $flush();
                $out[] = $line;
                continue;
            }

            if ($prev === $line) {
                ++$count;
                continue;
            }

            $flush();
            $prev = $line;
            $count = 1;
        }

        $flush();

        $omissions = [];
        $visible = implode("\n", $out);

        if ($granularity === 'fine') {
            $omissions = $fineOmissions;
        } elseif ($coarseParts !== []) {
            $placeholder = '{{REF_0}}';
            $visible .= "\n\nRepeated informational logs omitted.\n" . $placeholder;
            $omissions[] = [
                'reason' => 'deduplicated repeated lines',
                'content' => implode("\n\n", $coarseParts),
                'marker_placeholder' => $placeholder,
            ];
        }

        return new OptimizeResult($visible, $omissions);
    }

    private function isHighSignal(string $line): bool
    {
        return preg_match('/\b(ERROR|CRITICAL|ALERT|EMERGENCY|WARNING|WARN|FATAL|EXCEPTION)\b/i', $line) === 1;
    }
}
