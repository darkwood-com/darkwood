<?php

declare(strict_types=1);

namespace App\Workflow;

/**
 * In-memory + JSON-file representation of a completed (or failed) workflow run.
 *
 * @phpstan-type RunArray array<string, mixed>
 */
final class WorkflowRun
{
    /**
     * @param RunArray $report
     */
    public function __construct(
        public readonly string $runId,
        public readonly array $report,
    ) {
    }

    /**
     * @param RunArray $report
     */
    public static function fromReport(array $report): self
    {
        return new self((string) $report['runId'], $report);
    }
}
