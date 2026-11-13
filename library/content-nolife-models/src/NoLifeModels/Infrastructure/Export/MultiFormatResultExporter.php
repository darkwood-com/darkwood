<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Export;

use App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport;
use App\NoLifeModels\Domain\Export\ExportFormat;
use App\NoLifeModels\Domain\Export\ResultExporterInterface;
use App\NoLifeModels\Domain\Model\InferenceMetrics;
use App\NoLifeModels\Domain\Model\ModelInferenceResult;
use App\NoLifeModels\Domain\Model\PromptComparisonResult;

/**
 * Stable JSON schema (v1) for prompt comparisons and benchmark matrices.
 *
 * @see exportPromptComparison for shape
 */
final class MultiFormatResultExporter implements ResultExporterInterface
{
    public function supports(ExportFormat $format): bool
    {
        return true;
    }

    public function exportPromptComparison(PromptComparisonResult $result, ExportFormat $format): string
    {
        return match ($format) {
            ExportFormat::Json => json_encode($this->comparisonToArray($result), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR),
            ExportFormat::Csv => $this->comparisonToCsv($result),
            ExportFormat::Markdown => $this->comparisonToMarkdown($result),
        };
    }

    public function exportBenchmarkReport(BenchmarkRunReport $report, ExportFormat $format): string
    {
        return match ($format) {
            ExportFormat::Json => json_encode($this->benchmarkToArray($report), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR),
            ExportFormat::Csv => $this->benchmarkToCsv($report),
            ExportFormat::Markdown => $this->benchmarkToMarkdown($report),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function comparisonToArray(PromptComparisonResult $result): array
    {
        return [
            'schema' => 'no_life_models.comparison',
            'version' => 1,
            'id' => $result->id,
            'comparedAt' => $result->comparedAt->format(\DATE_ATOM),
            'prompt' => $result->prompt,
            'system' => $result->system,
            'models' => array_map(fn (ModelInferenceResult $r) => $this->inferenceToArray($r), $result->results),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function inferenceToArray(ModelInferenceResult $r): array
    {
        return [
            'model' => $r->modelName,
            'response' => $r->responseText,
            'error' => $r->error,
            'metrics' => $this->metricsToArray($r->metrics),
            'raw' => $r->rawResponse,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricsToArray(InferenceMetrics $m): array
    {
        return [
            'wallClockSeconds' => $m->wallClockSeconds,
            'totalDurationSeconds' => $m->totalDurationSeconds,
            'loadDurationSeconds' => $m->loadDurationSeconds,
            'promptEvalDurationSeconds' => $m->promptEvalDurationSeconds,
            'evalDurationSeconds' => $m->evalDurationSeconds,
            'promptEvalCount' => $m->promptEvalCount,
            'evalCount' => $m->evalCount,
            'tokensPerSecond' => $m->tokensPerSecond,
            'doneReason' => $m->doneReason,
        ];
    }

    private function comparisonToCsv(PromptComparisonResult $result): string
    {
        $fh = fopen('php://temp', 'r+');
        if (false === $fh) {
            throw new \RuntimeException('Cannot open temp stream.');
        }
        fputcsv($fh, ['comparison_id', 'model', 'error', 'wall_s', 'total_s', 'load_s', 'prompt_eval', 'eval_tokens', 'tps', 'response']);
        foreach ($result->results as $r) {
            $m = $r->metrics;
            fputcsv($fh, [
                $result->id,
                $r->modelName,
                $r->error ?? '',
                (string) $m->wallClockSeconds,
                self::nullableNum($m->totalDurationSeconds),
                self::nullableNum($m->loadDurationSeconds),
                self::nullableInt($m->promptEvalCount),
                self::nullableInt($m->evalCount),
                self::nullableNum($m->tokensPerSecond),
                $r->responseText,
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);

        return $csv;
    }

    private function comparisonToMarkdown(PromptComparisonResult $result): string
    {
        $lines = [
            '# NoLife Models — comparison',
            '',
            '- **id:** `'.$result->id.'`',
            '- **compared at:** '.$result->comparedAt->format(\DATE_ATOM),
            '',
            '## Prompt',
            '',
            '```',
            $result->prompt,
            '```',
            '',
        ];
        if (null !== $result->system && $result->system !== '') {
            $lines[] = '## System';
            $lines[] = '';
            $lines[] = '```';
            $lines[] = $result->system;
            $lines[] = '```';
            $lines[] = '';
        }
        foreach ($result->results as $r) {
            $lines[] = '## '.$r->modelName;
            $lines[] = '';
            $m = $r->metrics;
            $lines[] = sprintf(
                '- wall: **%.3f s** · total: **%s** · load: **%s** · prompt_eval: **%s** · eval: **%s** · tps: **%s**',
                $m->wallClockSeconds,
                self::nullableNum($m->totalDurationSeconds),
                self::nullableNum($m->loadDurationSeconds),
                self::nullableInt($m->promptEvalCount),
                self::nullableInt($m->evalCount),
                self::nullableNum($m->tokensPerSecond),
            );
            if (null !== $r->error && $r->error !== '') {
                $lines[] = '- **error:** '.$r->error;
            }
            $lines[] = '';
            $lines[] = $r->responseText;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    private function benchmarkToArray(BenchmarkRunReport $report): array
    {
        return [
            'schema' => 'no_life_models.benchmark',
            'version' => 1,
            'id' => $report->id,
            'startedAt' => $report->startedAt->format(\DATE_ATOM),
            'finishedAt' => $report->finishedAt->format(\DATE_ATOM),
            'cells' => array_map(fn ($c) => [
                'caseId' => $c->caseId,
                'model' => $c->modelName,
                'response' => $c->responseText,
                'error' => $c->error,
                'metrics' => $this->metricsToArray($c->metrics),
            ], $report->cells),
        ];
    }

    private function benchmarkToCsv(BenchmarkRunReport $report): string
    {
        $fh = fopen('php://temp', 'r+');
        if (false === $fh) {
            throw new \RuntimeException('Cannot open temp stream.');
        }
        fputcsv($fh, ['run_id', 'case_id', 'model', 'error', 'wall_s', 'eval_tokens', 'tps', 'response']);
        foreach ($report->cells as $c) {
            $m = $c->metrics;
            fputcsv($fh, [
                $report->id,
                $c->caseId,
                $c->modelName,
                $c->error ?? '',
                (string) $m->wallClockSeconds,
                self::nullableInt($m->evalCount),
                self::nullableNum($m->tokensPerSecond),
                $c->responseText,
            ]);
        }
        rewind($fh);
        $csv = stream_get_contents($fh) ?: '';
        fclose($fh);

        return $csv;
    }

    private function benchmarkToMarkdown(BenchmarkRunReport $report): string
    {
        $lines = [
            '# NoLife Models — benchmark run',
            '',
            '- **id:** `'.$report->id.'`',
            '- **started:** '.$report->startedAt->format(\DATE_ATOM),
            '- **finished:** '.$report->finishedAt->format(\DATE_ATOM),
            '',
            '| case | model | wall_s | eval | tps | error |',
            '| --- | --- | --- | --- | --- | --- |',
        ];
        foreach ($report->cells as $c) {
            $m = $c->metrics;
            $lines[] = sprintf(
                '| %s | %s | %.3f | %s | %s | %s |',
                $c->caseId,
                $c->modelName,
                $m->wallClockSeconds,
                self::nullableInt($m->evalCount),
                self::nullableNum($m->tokensPerSecond),
                str_replace('|', '\\|', (string) ($c->error ?? '')),
            );
        }
        $lines[] = '';
        $lines[] = '## Responses';
        $lines[] = '';
        foreach ($report->cells as $c) {
            $lines[] = '### '.$c->caseId.' · '.$c->modelName;
            $lines[] = '';
            $lines[] = $c->responseText;
            $lines[] = '';
        }

        return implode("\n", $lines);
    }

    private static function nullableNum(?float $v): string
    {
        return null === $v ? '' : (string) $v;
    }

    private static function nullableInt(?int $v): string
    {
        return null === $v ? '' : (string) $v;
    }
}
