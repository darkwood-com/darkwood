<?php

declare(strict_types=1);

namespace App\Service;

use App\Flow\DepthInferenceFlow;
use App\Model\DepthResult;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DepthAnythingService
{
    public function __construct(
        private readonly DepthInferenceFlow $flow,
    ) {
    }

    public function process(UploadedFile $upload, string $variant = 'q4_k'): DepthResult
    {
        return $this->flow->run($upload, $variant);
    }
}
