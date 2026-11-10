<?php

namespace App\Integration\Navi;

final class DarkwoodNaviTraceClient implements NaviTraceClientInterface
{
    /** @var array<string, TraceResult> */
    private array $traces = [];

    public function __construct(
        private readonly string $runBaseDir,
    ) {
    }

    public function name(): string
    {
        return 'darkwood_navi';
    }

    public function startOrAttachTrace(string $runId, array $context): string
    {
        error_log('Navi startRun');
        $traceId = sprintf('navi-trace-%s', $runId);
        if (!isset($this->traces[$traceId])) {
            $this->traces[$traceId] = new TraceResult($traceId, $runId, [], 'running');
            $this->persist($traceId);
        }

        return $traceId;
    }

    public function recordStep(string $traceId, string $stepName, array $payload = []): void
    {
        error_log('Navi appendEvent');
        $trace = $this->traces[$traceId] ?? new TraceResult($traceId, $this->extractRunId($traceId), [], 'running');
        $events = [...$trace->events, new TraceEvent($stepName, (new \DateTimeImmutable())->format(\DATE_ATOM), $payload)];
        $this->traces[$traceId] = new TraceResult($trace->traceId, $trace->runId, $events, $trace->status);
        $this->persist($traceId);
    }

    public function finishTrace(string $traceId, array $payload = []): void
    {
        error_log('Navi finishRun');
        $trace = $this->traces[$traceId] ?? new TraceResult($traceId, $this->extractRunId($traceId), [], 'running');
        $events = [...$trace->events, new TraceEvent('trace.finished', (new \DateTimeImmutable())->format(\DATE_ATOM), $payload)];
        $this->traces[$traceId] = new TraceResult($trace->traceId, $trace->runId, $events, 'completed');
        $this->persist($traceId);
    }

    private function extractRunId(string $traceId): string
    {
        return str_replace('navi-trace-', '', $traceId);
    }

    private function persist(string $traceId): void
    {
        $trace = $this->traces[$traceId];
        $runDir = rtrim($this->runBaseDir, '/').'/'.$trace->runId;
        if (!is_dir($runDir)) {
            mkdir($runDir, 0775, true);
        }

        $tmpPath = $runDir.'/trace.json.tmp';
        $finalPath = $runDir.'/trace.json';
        file_put_contents($tmpPath, json_encode($trace->toArray(), \JSON_PRETTY_PRINT | \JSON_THROW_ON_ERROR));
        rename($tmpPath, $finalPath);
    }
}
