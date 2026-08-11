<?php

declare(strict_types=1);

namespace Quake3\Collision;

/**
 * Subset of trace_t from q_shared.h / cm_trace.c.
 */
final class TraceResult
{
    /** @param list<float> $endPos @param list<float>|null $planeNormal */
    public function __construct(
        public float $fraction = 1.0,
        public array $endPos = [0.0, 0.0, 0.0],
        public ?array $planeNormal = null,
        public float $planeDist = 0.0,
        public bool $startSolid = false,
        public bool $allSolid = false,
        public int $contents = 0,
        public int $surfaceFlags = 0,
        public int $entityNum = 0,
    ) {
    }
}
