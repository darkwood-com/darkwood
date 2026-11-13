<?php

declare(strict_types=1);

namespace App\NoLifeModels\Infrastructure\Session;

use App\NoLifeModels\Domain\Model\InferenceMetrics;
use App\NoLifeModels\Domain\Model\ModelInferenceResult;
use App\NoLifeModels\Domain\Model\PromptComparisonResult;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Persists prompt comparison payloads in the HTTP session as plain arrays.
 */
final class ComparisonSessionStore
{
    private const SESSION_KEY = 'no_life_models.comparisons';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function save(PromptComparisonResult $result): void
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get(self::SESSION_KEY, []);
        if (!\is_array($bag)) {
            $bag = [];
        }
        $bag[$result->id] = $this->toArray($result);
        $session->set(self::SESSION_KEY, $bag);
    }

    public function load(string $id): ?PromptComparisonResult
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get(self::SESSION_KEY, []);
        if (!\is_array($bag) || !isset($bag[$id]) || !\is_array($bag[$id])) {
            return null;
        }

        return $this->fromArray($bag[$id]);
    }

    /**
     * @return list<string> comparison ids newest first (limited)
     */
    public function listRecentIds(int $limit = 20): array
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get(self::SESSION_KEY, []);
        if (!\is_array($bag)) {
            return [];
        }
        $ids = array_keys($bag);
        $ids = array_reverse($ids);

        return \array_slice($ids, 0, $limit);
    }

    public function saveBenchmarkReport(\App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport $report): void
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get('no_life_models.benchmarks', []);
        if (!\is_array($bag)) {
            $bag = [];
        }
        $bag[$report->id] = $this->benchmarkToArray($report);
        $session->set('no_life_models.benchmarks', $bag);
    }

    public function loadBenchmarkReport(string $id): ?\App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get('no_life_models.benchmarks', []);
        if (!\is_array($bag) || !isset($bag[$id]) || !\is_array($bag[$id])) {
            return null;
        }

        return $this->benchmarkFromArray($bag[$id]);
    }

    /**
     * @return list<string>
     */
    public function listBenchmarkIds(int $limit = 20): array
    {
        $session = $this->requestStack->getSession();
        $bag = $session->get('no_life_models.benchmarks', []);
        if (!\is_array($bag)) {
            return [];
        }
        $ids = array_keys($bag);
        $ids = array_reverse($ids);

        return \array_slice($ids, 0, $limit);
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(PromptComparisonResult $r): array
    {
        $results = [];
        foreach ($r->results as $row) {
            $results[] = [
                'modelName' => $row->modelName,
                'responseText' => $row->responseText,
                'error' => $row->error,
                'metrics' => $this->metricsToArray($row->metrics),
                'rawResponse' => $row->rawResponse,
            ];
        }

        return [
            'id' => $r->id,
            'prompt' => $r->prompt,
            'system' => $r->system,
            'comparedAt' => $r->comparedAt->format(\DATE_ATOM),
            'results' => $results,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function fromArray(array $data): PromptComparisonResult
    {
        $results = [];
        foreach ($data['results'] as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $m = $row['metrics'];
            $metrics = new InferenceMetrics(
                (float) ($m['wallClockSeconds'] ?? 0.0),
                isset($m['totalDurationSeconds']) ? (float) $m['totalDurationSeconds'] : null,
                isset($m['loadDurationSeconds']) ? (float) $m['loadDurationSeconds'] : null,
                isset($m['promptEvalDurationSeconds']) ? (float) $m['promptEvalDurationSeconds'] : null,
                isset($m['evalDurationSeconds']) ? (float) $m['evalDurationSeconds'] : null,
                isset($m['promptEvalCount']) ? (int) $m['promptEvalCount'] : null,
                isset($m['evalCount']) ? (int) $m['evalCount'] : null,
                isset($m['tokensPerSecond']) ? (float) $m['tokensPerSecond'] : null,
                isset($m['doneReason']) ? (string) $m['doneReason'] : null,
            );
            $raw = $row['rawResponse'] ?? null;
            $results[] = new ModelInferenceResult(
                (string) $row['modelName'],
                (string) $row['responseText'],
                isset($row['error']) ? (string) $row['error'] : null,
                $metrics,
                \is_array($raw) ? $raw : null,
            );
        }

        return new PromptComparisonResult(
            (string) $data['id'],
            (string) $data['prompt'],
            isset($data['system']) ? (string) $data['system'] : null,
            new \DateTimeImmutable((string) $data['comparedAt']),
            $results,
        );
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

    /**
     * @return array<string, mixed>
     */
    private function benchmarkToArray(\App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport $r): array
    {
        $cells = [];
        foreach ($r->cells as $c) {
            $cells[] = [
                'caseId' => $c->caseId,
                'modelName' => $c->modelName,
                'responseText' => $c->responseText,
                'error' => $c->error,
                'metrics' => $this->metricsToArray($c->metrics),
            ];
        }

        return [
            'id' => $r->id,
            'startedAt' => $r->startedAt->format(\DATE_ATOM),
            'finishedAt' => $r->finishedAt->format(\DATE_ATOM),
            'cells' => $cells,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function benchmarkFromArray(array $data): \App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport
    {
        $cells = [];
        foreach ($data['cells'] as $row) {
            if (!\is_array($row)) {
                continue;
            }
            $m = $row['metrics'];
            $metrics = new InferenceMetrics(
                (float) ($m['wallClockSeconds'] ?? 0.0),
                isset($m['totalDurationSeconds']) ? (float) $m['totalDurationSeconds'] : null,
                isset($m['loadDurationSeconds']) ? (float) $m['loadDurationSeconds'] : null,
                isset($m['promptEvalDurationSeconds']) ? (float) $m['promptEvalDurationSeconds'] : null,
                isset($m['evalDurationSeconds']) ? (float) $m['evalDurationSeconds'] : null,
                isset($m['promptEvalCount']) ? (int) $m['promptEvalCount'] : null,
                isset($m['evalCount']) ? (int) $m['evalCount'] : null,
                isset($m['tokensPerSecond']) ? (float) $m['tokensPerSecond'] : null,
                isset($m['doneReason']) ? (string) $m['doneReason'] : null,
            );
            $cells[] = new \App\NoLifeModels\Domain\Benchmark\BenchmarkCellResult(
                (string) $row['caseId'],
                (string) $row['modelName'],
                (string) $row['responseText'],
                isset($row['error']) ? (string) $row['error'] : null,
                $metrics,
            );
        }

        return new \App\NoLifeModels\Domain\Benchmark\BenchmarkRunReport(
            (string) $data['id'],
            new \DateTimeImmutable((string) $data['startedAt']),
            new \DateTimeImmutable((string) $data['finishedAt']),
            $cells,
        );
    }
}
