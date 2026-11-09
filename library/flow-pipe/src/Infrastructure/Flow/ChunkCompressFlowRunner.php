<?php

declare(strict_types=1);

namespace App\Infrastructure\Flow;

use App\Infrastructure\Flow\Model\ChunkMergeCollector;
use App\Infrastructure\Flow\Model\ChunkTask;
use App\TokenFlow\PipelineContext;
use App\TokenFlow\Step\CompressChunkStep;
use Flow\DriverInterface;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\IpStrategy\MaxIpStrategy;
use Flow\Job\ClosureJob;
use Generator;

final class ChunkCompressFlowRunner
{
    public function __construct(
        private readonly DriverInterface $driver,
    ) {}

    public function run(PipelineContext $context, CompressChunkStep $compressStep): PipelineContext
    {
        if ($context->chunks === []) {
            return $compressStep->apply($context);
        }

        $collector = new ChunkMergeCollector();

        $flow = (new FlowFactory())->create(function () use ($compressStep, $collector): Generator {
            $flow = yield [
                new ClosureJob(function (ChunkTask $task) use ($compressStep, $collector): ChunkTask {
                    $compressed = $compressStep->applyToChunk($task->content);
                    $collector->store($task->index, $compressed);

                    return new ChunkTask($task->index, $compressed);
                }),
                null,
                new MaxIpStrategy(4),
            ];

            return $flow;
        }, ['driver' => $this->driver]);

        foreach ($context->chunks as $index => $chunk) {
            $flow(new Ip(new ChunkTask($index, $chunk)));
        }

        $flow->await();

        $context->chunks = $collector->ordered();
        $context->recordStep($compressStep->displayName());
        $context->addDebug(sprintf(
            'compress (flow fan-out) processed %d chunks via %s',
            count($context->chunks),
        ));

        return $context;
    }
}
