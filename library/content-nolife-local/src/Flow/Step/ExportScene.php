<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Bridge\NativeDepthBridge;
use App\Flow\Model\DepthPipelineState;
use Flow\JobInterface;

/**
 * @implements JobInterface<DepthPipelineState, DepthPipelineState>
 */
final class ExportScene implements JobInterface
{
    public function __construct(
        private readonly NativeDepthBridge $bridge,
    ) {
    }

    public function __invoke($state): DepthPipelineState
    {
        $state->glbPath = $state->workDir.'/scene.glb';
        $this->bridge->exportGlbCached($state->glbPath);

        return $state;
    }
}
