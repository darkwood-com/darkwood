<?php

namespace App\Integration\Navi;

interface NaviTraceClient
{
    public function name(): string;

    /**
     * @param array<string, mixed> $context
     */
    public function startOrAttachTrace(string $runId, array $context): string;

    /**
     * @param array<string, mixed> $payload
     */
    public function recordStep(string $traceId, string $stepName, array $payload = []): void;

    /**
     * @param array<string, mixed> $payload
     */
    public function finishTrace(string $traceId, array $payload = []): void;
}
