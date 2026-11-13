<?php

declare(strict_types=1);

namespace App\Workflow;

use RuntimeException;

/**
 * Minimal atomic JSON persistence under var/runs. No repository abstraction.
 */
final class WorkflowRunStore
{
    public function __construct(
        private readonly string $directory,
    ) {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Unable to create runs directory.');
        }
    }

    public function save(WorkflowRun $run): void
    {
        $path = $this->pathFor($run->runId);
        $json = json_encode($run->report, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $dir = dirname($path);
        $temp = tempnam($dir, 'run_');
        if ($temp === false) {
            throw new RuntimeException('Unable to allocate temporary run file.');
        }

        if (file_put_contents($temp, $json . "\n", LOCK_EX) === false) {
            @unlink($temp);
            throw new RuntimeException('Unable to write temporary run file.');
        }

        if (!rename($temp, $path)) {
            @unlink($temp);
            throw new RuntimeException('Unable to atomically persist run file.');
        }
    }

    public function get(string $runId): ?WorkflowRun
    {
        if (!$this->isSafeRunId($runId)) {
            return null;
        }

        $path = $this->pathFor($runId);
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        /** @var array<string, mixed> $report */
        $report = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return WorkflowRun::fromReport($report);
    }

    private function pathFor(string $runId): string
    {
        return $this->directory . DIRECTORY_SEPARATOR . $runId . '.json';
    }

    private function isSafeRunId(string $runId): bool
    {
        return (bool) preg_match('/^[A-Za-z0-9_-]{8,64}$/', $runId);
    }
}
