<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Flow\Model\DepthPipelineState;
use App\Model\DepthResult;
use Flow\JobInterface;

/**
 * @implements JobInterface<DepthPipelineState, DepthPipelineState>
 */
final class BuildResult implements JobInterface
{
    public function __invoke($state): DepthPipelineState
    {
        if ($state->failure !== null) {
            return $state;
        }

        $start = hrtime(true);
        $dense = $state->dense;
        $extrinsics = $this->toMatrix3x4($dense['extrinsics']);
        $intrinsics = $this->toMatrix3x3($dense['intrinsics']);
        $state->timings['result'] = (hrtime(true) - $start) / 1_000_000;

        $glbPath = is_file($state->glbPath) ? $state->glbPath : null;
        $modelBytes = ($state->modelPath !== null && is_file($state->modelPath))
            ? filesize($state->modelPath)
            : false;
        $glbBytes = ($glbPath !== null) ? filesize($glbPath) : false;

        $state->result = new DepthResult(
            id: $state->id,
            width: $dense['width'],
            height: $dense['height'],
            sourceWidth: $state->sourceSize[0],
            sourceHeight: $state->sourceSize[1],
            originalImagePath: $state->inputPath,
            originalAssetName: $state->inputFilename,
            depthImagePath: $state->depthPath,
            confidenceImagePath: $state->confidencePath,
            extrinsics: $extrinsics,
            intrinsics: $intrinsics,
            glbPath: $glbPath,
            durationMs: $state->timings['infer'] ?? 0.0,
            totalPipelineMs: array_sum($state->timings),
            loadMs: $dense['loadMs'],
            modelLabel: $state->modelLabel,
            backend: $state->backendLabel,
            pointCount: $dense['width'] * $dense['height'],
            isMetric: $dense['isMetric'],
            depthMin: $state->depthMin,
            depthMax: $state->depthMax,
            pipelineTimings: $state->timings,
            modelBytes: $modelBytes === false ? null : $modelBytes,
            glbBytes: $glbBytes === false ? null : $glbBytes,
        );

        $this->writeMetadata($state);

        return $state;
    }

    private function writeMetadata(DepthPipelineState $state): void
    {
        $result = $state->result;
        if ($result === null || $state->workDir === null) {
            return;
        }

        $payload = [
            'id' => $result->id,
            'model' => $result->modelLabel,
            'variant' => $state->variant,
            'backend' => $result->backend,
            'localInference' => true,
            'networkAiRequests' => 0,
            'source' => ['width' => $result->sourceWidth, 'height' => $result->sourceHeight],
            'processed' => ['width' => $result->width, 'height' => $result->height],
            'depth' => [
                'min' => $result->depthMin,
                'max' => $result->depthMax,
                'metric' => $result->isMetric,
                'units' => $result->isMetric ? 'meters' : 'relative',
            ],
            'camera' => $result->cameraSummary(),
            'timingsMs' => $result->pipelineTimings,
            'inferMs' => $result->durationMs,
            'totalPipelineMs' => $result->totalPipelineMs,
            'orchestrationMs' => $result->orchestrationMs(),
            'loadMs' => $result->loadMs,
            'modelBytes' => $result->modelBytes,
            'glbBytes' => $result->glbBytes,
            'pointCount' => $result->pointCount,
            'outputs' => [
                'input' => $result->originalAssetName,
                'depth' => 'depth.png',
                'confidence' => $result->confidenceImagePath !== null ? 'confidence.png' : null,
                'glb' => $result->glbPath !== null ? 'scene.glb' : null,
                'metadata' => 'metadata.json',
            ],
        ];

        file_put_contents(
            $state->workDir.'/metadata.json',
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n",
        );
    }

    /** @param list<float> $flat */
    private function toMatrix3x4(array $flat): ?array
    {
        if (count($flat) !== 12) {
            return null;
        }

        return [
            array_slice($flat, 0, 4),
            array_slice($flat, 4, 4),
            array_slice($flat, 8, 4),
        ];
    }

    /** @param list<float> $flat */
    private function toMatrix3x3(array $flat): ?array
    {
        if (count($flat) !== 9) {
            return null;
        }

        return [
            array_slice($flat, 0, 3),
            array_slice($flat, 3, 3),
            array_slice($flat, 6, 3),
        ];
    }
}
