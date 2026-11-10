<?php

declare(strict_types=1);

namespace App\Flow\Step;

use App\Flow\Model\DepthPipelineState;
use App\Service\DepthColormapRenderer;
use Flow\JobInterface;

/**
 * @implements JobInterface<DepthPipelineState, DepthPipelineState>
 */
final class GenerateDepthPreview implements JobInterface
{
    public function __construct(
        private readonly DepthColormapRenderer $colormap,
    ) {
    }

    public function __invoke($state): DepthPipelineState
    {
        $dense = $state->dense;
        $state->depthMin = min($dense['depth']);
        $state->depthMax = max($dense['depth']);

        $depthPng = $this->colormap->toTurboPng(
            $dense['depth'],
            $dense['width'],
            $dense['height'],
            $state->depthMin,
            $state->depthMax,
        );
        $state->depthPath = $state->workDir.'/depth.png';
        file_put_contents($state->depthPath, $depthPng);

        if ($dense['confidence'] !== null) {
            $confPng = $this->colormap->toGrayscalePng($dense['confidence'], $dense['width'], $dense['height'], false);
            $state->confidencePath = $state->workDir.'/confidence.png';
            file_put_contents($state->confidencePath, $confPng);
        }

        return $state;
    }
}
