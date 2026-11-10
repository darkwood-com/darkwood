<?php

declare(strict_types=1);

namespace App\Flow\Model;

use App\Model\DepthResult;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class DepthPipelineState
{
    /** @var array<string, float> */
    public array $timings = [];

    public ?string $id = null;
    public ?string $workDir = null;
    public ?string $inputPath = null;
    public ?string $inputFilename = null;
    public ?string $modelPath = null;
    public ?string $modelLabel = null;

    /** @var array{0:int,1:int}|null */
    public ?array $sourceSize = null;

    /** @var array<string, mixed>|null */
    public ?array $dense = null;

    public ?string $depthPath = null;
    public ?string $confidencePath = null;
    public ?string $glbPath = null;

    public ?float $depthMin = null;
    public ?float $depthMax = null;

    public ?DepthResult $result = null;
    public ?string $failure = null;

    public function __construct(
        public readonly UploadedFile $upload,
        public readonly string $variant,
        public readonly string $modelPathQ4k,
        public readonly string $modelPathF32,
        public readonly string $inferenceDir,
        public readonly string $backendLabel,
    ) {
    }
}
