<?php

declare(strict_types=1);

namespace App\Workflow;

/**
 * Mutable pipeline data carried as Flow\Ip::$data through change-review jobs.
 */
final class ChangeReviewContext
{
    public string $runId;

    public string $workflow = 'change-review';

    public string $status = 'running';

    public string $startedAt;

    public ?string $finishedAt = null;

    public float $startedAtMono;

    /** @var array<string, mixed> */
    public array $input;

    /** @var list<array<string, mixed>> */
    public array $steps = [];

    /** @var array<string, mixed> */
    public array $result = [];

    /** @var array<string, mixed> */
    public array $retrospective = [];

    /** @var list<string> */
    public array $warnings = [];

    public ?string $failureCode = null;

    public ?string $failureMessage = null;

    /**
     * @param array<string, mixed> $input
     */
    public function __construct(string $runId, array $input)
    {
        $this->runId = $runId;
        $this->input = $input;
        $this->startedAt = gmdate('c');
        $this->startedAtMono = hrtime(true) / 1e6;
    }

    /**
     * @param array<string, mixed> $output
     */
    public function beginStep(string $name): float
    {
        return hrtime(true) / 1e6;
    }

    /**
     * @param array<string, mixed> $output
     */
    public function completeStep(string $name, float $startedMs, array $output = [], string $status = 'completed'): void
    {
        $this->steps[] = [
            'name' => $name,
            'status' => $status,
            'durationMs' => round((hrtime(true) / 1e6) - $startedMs, 3),
            'output' => $output,
        ];
    }

    public function fail(string $code, string $message): void
    {
        $this->status = 'failed';
        $this->failureCode = $code;
        $this->failureMessage = $message;
    }

    public function finish(string $status = 'completed'): void
    {
        if ($this->status === 'running') {
            $this->status = $status;
        }
        $this->finishedAt = gmdate('c');
    }

    /**
     * @return array<string, mixed>
     */
    public function toReport(): array
    {
        $finishedMono = hrtime(true) / 1e6;

        return [
            'runId' => $this->runId,
            'workflow' => $this->workflow,
            'status' => $this->status,
            'startedAt' => $this->startedAt,
            'finishedAt' => $this->finishedAt ?? gmdate('c'),
            'durationMs' => round(($this->finishedAt !== null ? (hrtime(true) / 1e6) : $finishedMono) - $this->startedAtMono, 3),
            'input' => $this->input,
            'steps' => $this->steps,
            'result' => $this->result,
            'retrospective' => $this->retrospective,
            'warnings' => $this->warnings,
            'failure' => $this->failureCode === null ? null : [
                'code' => $this->failureCode,
                'message' => $this->failureMessage,
            ],
        ];
    }
}
