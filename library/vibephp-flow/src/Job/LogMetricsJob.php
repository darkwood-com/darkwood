<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\VibeRequest;
use Flow\JobInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @implements JobInterface<VibeRequest, array{status: int, duration: float, memory: string}>
 */
final class LogMetricsJob implements JobInterface
{
    public function __construct(
        private readonly SymfonyStyle $io,
    ) {}

    public function __invoke($data): array
    {
        $this->io->section('[metrics] request complete');
        $duration = microtime(true) - $data->startedAt;
        $status = $data->response?->status ?? 0;

        $this->io->table(
            ['Metric', 'Value'],
            [
                ['Method', $data->method],
                ['Path', $data->path],
                ['Script', $data->script ?? '—'],
                ['Status', (string) $status],
                ['Duration', sprintf('%.2f ms', $duration * 1000)],
                ['Peak memory', $this->formatBytes(memory_get_peak_usage(true))],
                ['Includes', (string) ($data->response?->includeCount ?? 0)],
            ]
        );

        return [
            'status' => $status,
            'duration' => $duration,
            'memory' => $this->formatBytes(memory_get_peak_usage(true)),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        return round($bytes / 1024 / 1024, 2).' MB';
    }
}
