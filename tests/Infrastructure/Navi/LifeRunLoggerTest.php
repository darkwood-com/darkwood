<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Navi;

use App\Infrastructure\Navi\LifeRunLogger;
use App\Infrastructure\Navi\LogBenchmarkAction;
use App\Infrastructure\Navi\LogChunkAction;
use App\Infrastructure\Navi\LogGenerationAction;
use App\Infrastructure\Navi\LogRunEventAction;
use Navi\Application\Workflow\WorkflowRunner;
use PHPUnit\Framework\TestCase;

final class LifeRunLoggerTest extends TestCase
{
    public function testItWritesVersionedRunEventWithIndexedFields(): void
    {
        $logPath = $this->logPath('run-event');
        $logger = $this->logger($logPath);

        $logger->log('run_1', 'training_started', [
            'mode' => 'supervised',
            'dataset' => 'beacon',
            'seed' => 42,
        ]);

        $record = $this->lastRecord($logPath);

        self::assertSame(1, $record['schema_version']);
        self::assertSame('run_1', $record['run_id']);
        self::assertSame('training_started', $record['event']);
        self::assertSame('supervised', $record['mode']);
        self::assertSame('beacon', $record['dataset']);
        self::assertSame(42, $record['seed']);
    }

    public function testItSlimsChunkPayloads(): void
    {
        $logPath = $this->logPath('chunk');
        $logger = $this->logger($logPath);

        $logger->logChunk('run_2', [
            'mode' => 'unsupervised',
            'chunk' => [
                'generation' => 3,
                'best_fitness' => 0.7,
                'average_fitness' => 0.4,
                'seed' => 99,
                'best_program' => [
                    'type' => 'sequence',
                    'nodes' => [
                        ['type' => 'birth', 'x' => 1, 'y' => 1],
                        ['type' => 'next'],
                    ],
                ],
                'best_frame' => [
                    'frame' => 1,
                    'size' => 2,
                    'cells' => [[1, 0], [0, 1]],
                ],
            ],
        ]);

        $record = $this->lastRecord($logPath);

        self::assertSame('chunk_recorded', $record['event']);
        self::assertSame(3, $record['generation']);
        self::assertSame(0.7, $record['fitness']);
        self::assertSame(2, $record['program_size']);
        self::assertArrayHasKey('sequence_hash', $record['payload']);
        self::assertArrayHasKey('frame_hash', $record['payload']);
        self::assertArrayNotHasKey('chunk', $record['payload']);
        self::assertArrayNotHasKey('best_program', $record['payload']);
        self::assertArrayNotHasKey('best_frame', $record['payload']);
    }

    public function testItUsesBenchmarkCompletedAndFailedEvents(): void
    {
        $logPath = $this->logPath('benchmark');
        $logger = $this->logger($logPath);

        $logger->logBenchmark('bench_1', [
            'mode' => 'unsupervised',
            'final_fitness' => 0.5,
            'sequence_hash' => 'abc',
        ]);
        $logger->logBenchmark('bench_2', [
            'event' => 'benchmark_failed',
            'error' => 'boom',
        ]);

        $records = $this->records($logPath);

        self::assertSame('benchmark_completed', $records[0]['event']);
        self::assertSame('abc', $records[0]['sequence_hash']);
        self::assertSame(0.5, $records[0]['fitness']);
        self::assertSame('benchmark_failed', $records[1]['event']);
        self::assertArrayNotHasKey('event', $records[1]['payload']);
    }

    private function logger(string $logPath): LifeRunLogger
    {
        return new LifeRunLogger(
            new WorkflowRunner(),
            new LogRunEventAction($logPath),
            new LogGenerationAction($logPath),
            new LogChunkAction($logPath),
            new LogBenchmarkAction($logPath),
        );
    }

    private function logPath(string $name): string
    {
        $path = sys_get_temp_dir() . sprintf('/llm_game_of_life_tests/navi/%s.ndjson', $name);
        @unlink($path);

        return $path;
    }

    /**
     * @return array<string,mixed>
     */
    private function lastRecord(string $logPath): array
    {
        $records = $this->records($logPath);

        return $records[\array_key_last($records)];
    }

    /**
     * @return list<array<string,mixed>>
     */
    private function records(string $logPath): array
    {
        $raw = file_get_contents($logPath);
        self::assertIsString($raw);

        return array_map(
            static fn (string $line): array => json_decode($line, true, 512, JSON_THROW_ON_ERROR),
            array_values(array_filter(array_map('trim', explode("\n", $raw)))),
        );
    }
}
