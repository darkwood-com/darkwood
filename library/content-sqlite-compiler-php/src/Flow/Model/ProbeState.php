<?php

declare(strict_types=1);

namespace App\Flow\Model;

use App\Compiler\Platform\ProcessResult;

final class ProbeState
{
    /** @var array<string, float> */
    public array $timings = [];

    public ?string $failure = null;
    public ?ProcessResult $compileResult = null;
    public ?ProcessResult $runResult = null;

    public function __construct(
        public readonly string $sourcePath,
        public readonly int $expectedExit,
        public readonly string $expectedStdout = '',
        public readonly string $workDir = '',
    ) {
    }
}
