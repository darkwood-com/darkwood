<?php

declare(strict_types=1);

namespace App\Flow;

use App\Flow\Model\DepthPipelineState;
use App\Flow\Step\BuildResult;
use App\Flow\Step\ExportScene;
use App\Flow\Step\GenerateDepthPreview;
use App\Flow\Step\InferDepth;
use App\Flow\Step\ValidateAndPrepareInput;
use App\Model\DepthResult;
use Flow\FlowFactory;
use Flow\Ip;
use Flow\JobInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DepthInferenceFlow
{
    public function __construct(
        private readonly ValidateAndPrepareInput $validateAndPrepare,
        private readonly InferDepth $inferDepth,
        private readonly GenerateDepthPreview $generateDepthPreview,
        private readonly ExportScene $exportScene,
        private readonly BuildResult $buildResult,
        private readonly string $modelPathQ4k,
        private readonly string $modelPathF32,
        private readonly string $inferenceDir,
        private readonly string $backendLabel,
    ) {
    }

    public function run(UploadedFile $upload, string $variant = 'q4_k'): DepthResult
    {
        $state = new DepthPipelineState(
            upload: $upload,
            variant: $variant,
            modelPathQ4k: $this->modelPathQ4k,
            modelPathF32: $this->modelPathF32,
            inferenceDir: $this->inferenceDir,
            backendLabel: $this->backendLabel,
        );

        $flow = (new FlowFactory())->create(function () {
            yield $this->timed('validate', $this->validateAndPrepare);
            yield $this->timed('infer', $this->inferDepth);
            yield $this->timed('colormap', $this->generateDepthPreview);
            yield $this->timed('glb', $this->exportScene);
            yield $this->buildResult;
        });

        $ip = new Ip($state);
        $flow($ip);
        $flow->await();

        $result = $state->result;
        if ($state->failure !== null) {
            throw new \RuntimeException($state->failure);
        }
        if ($result === null) {
            throw new \RuntimeException('Depth inference flow did not produce a result.');
        }

        return $result;
    }

    /**
     * @param JobInterface<DepthPipelineState, DepthPipelineState> $job
     *
     * @return \Closure(DepthPipelineState): DepthPipelineState
     */
    private function timed(string $name, JobInterface $job): \Closure
    {
        return function (DepthPipelineState $state) use ($name, $job): DepthPipelineState {
            if ($state->failure !== null) {
                return $state;
            }

            $start = hrtime(true);
            try {
                $job($state);
            } catch (\Throwable $e) {
                $state->failure = $e->getMessage();
            }
            $state->timings[$name] = (hrtime(true) - $start) / 1_000_000;

            return $state;
        };
    }
}
