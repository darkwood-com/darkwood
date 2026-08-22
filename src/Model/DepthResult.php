<?php

declare(strict_types=1);

namespace App\Model;

final readonly class DepthResult
{
    /**
     * @param list<list<float>>|null $extrinsics 3×4 row-major
     * @param list<list<float>>|null $intrinsics 3×3 row-major
     */
    public function __construct(
        public string $id,
        public int $width,
        public int $height,
        public int $sourceWidth,
        public int $sourceHeight,
        public string $originalImagePath,
        public string $originalAssetName,
        public string $depthImagePath,
        public ?string $confidenceImagePath,
        public ?array $extrinsics,
        public ?array $intrinsics,
        public ?string $glbPath,
        public float $durationMs,
        public float $totalPipelineMs,
        public float $loadMs,
        public string $modelLabel,
        public string $backend,
        public int $pointCount,
        public bool $isMetric,
        public float $depthMin,
        public float $depthMax,
        /** @var array<string, float> */
        public array $pipelineTimings = [],
        public ?int $modelBytes = null,
        public ?int $glbBytes = null,
    ) {
    }

    /** Non-native Flow steps from the same run (total − infer). */
    public function orchestrationMs(): float
    {
        return max(0.0, $this->totalPipelineMs - $this->durationMs);
    }

    /** @return array{fx: float, fy: float, cx: float, cy: float}|null */
    public function cameraSummary(): ?array
    {
        if ($this->intrinsics === null || count($this->intrinsics) !== 3) {
            return null;
        }

        $row0 = $this->intrinsics[0] ?? null;
        $row1 = $this->intrinsics[1] ?? null;
        if (!is_array($row0) || !is_array($row1) || count($row0) < 3 || count($row1) < 3) {
            return null;
        }

        return [
            'fx' => (float) $row0[0],
            'fy' => (float) $row1[1],
            'cx' => (float) $row0[2],
            'cy' => (float) $row1[2],
        ];
    }
}
