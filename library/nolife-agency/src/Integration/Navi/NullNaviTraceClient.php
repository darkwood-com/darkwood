<?php

namespace App\Integration\Navi;

final class NullNaviTraceClient implements NaviTraceClientInterface
{
    public function name(): string
    {
        return 'null';
    }

    public function startOrAttachTrace(string $runId, array $context): string
    {
        return sprintf('local-trace-%s', $runId);
    }

    public function recordStep(string $traceId, string $stepName, array $payload = []): void
    {
    }

    public function finishTrace(string $traceId, array $payload = []): void
    {
    }
}
