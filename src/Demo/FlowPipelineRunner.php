<?php

declare(strict_types=1);

namespace App\Demo;

use App\Demo\Flow\FetchImageJob;
use App\Demo\Flow\HashBinaryJob;
use App\Demo\Flow\ResultCollector;
use App\Demo\Flow\SaveFileJob;
use App\Demo\Model\FetchRequest;
use App\Demo\Model\ImageResult;
use App\Demo\Model\Summary;
use Flow\Driver\StreamSelectDriver;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;

/**
 * Runs the demo pipeline on darkwood/flow (experimental StreamSelectDriver + MaxIpStrategy).
 * FetchImageJob is a Generator that yields stream wait tokens.
 */
final class FlowPipelineRunner
{
    public function __construct(
        private readonly string $outputDir,
    ) {
    }

    /**
     * @param list<string> $urls
     */
    public function run(
        array $urls,
        int $concurrency,
        float $timeoutSeconds,
        Trace $trace,
    ): Summary {
        $startedAt = microtime(true);
        $trace->emit('flow.started', [
            'items' => count($urls),
            'concurrency' => $concurrency,
        ]);

        $driver = new StreamSelectDriver();
        $collector = new ResultCollector();
        $fetchJob = new FetchImageJob($driver, $trace);
        $hashJob = new HashBinaryJob();
        $saveJob = new SaveFileJob($this->outputDir, $trace);

        $flow = (new FlowFactory($driver))->create(static function () use ($fetchJob, $hashJob, $saveJob, $collector, $concurrency) {
            yield [$fetchJob, null, new MaxIpStrategy($concurrency)];
            yield $hashJob;
            yield $saveJob;
            yield static function (ImageResult $result) use ($collector): ImageResult {
                $collector->add($result);

                return $result;
            };
        });

        foreach ($urls as $url) {
            $flow(new Ip(new FetchRequest($url, $timeoutSeconds)));
        }

        $flow->await();

        $results = $collector->all();
        $ok = count(array_filter($results, static fn (ImageResult $r): bool => $r->success));
        $failed = count($results) - $ok;
        $elapsedMs = (microtime(true) - $startedAt) * 1000;

        $summary = new Summary(
            results: $results,
            ok: $ok,
            failed: $failed,
            elapsedMs: $elapsedMs,
            concurrency: $concurrency,
        );

        $trace->emit('flow.completed', [
            'ok' => $ok,
            'failed' => $failed,
            'elapsed_ms' => round($elapsedMs, 2),
        ]);

        return $summary;
    }
}
