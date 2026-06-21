<?php

declare(strict_types=1);

namespace App\TokenFlow;

final class PipelineContext
{
    public string $stream = '';

    /** @var list<string> */
    public array $chunks = [];

    public int $originalCharCount = 0;

    public int $originalTokenEstimate = 0;

    public int $compressedCharCount = 0;

    public int $compressedTokenEstimate = 0;

    /** @var list<string> */
    public array $executedSteps = [];

    public ?int $budget = null;

    /** @var list<string> */
    public array $debug = [];

    public string $inputKey = '';

    public bool $debugEnabled = false;

    public function recordStep(string $stepName): void
    {
        $this->executedSteps[] = $stepName;
    }

    public function addDebug(string $message): void
    {
        if ($this->debugEnabled) {
            $this->debug[] = $message;
        }
    }

    public function reductionPercent(): float
    {
        if ($this->originalTokenEstimate === 0) {
            return 0.0;
        }

        return round(
            (1 - ($this->compressedTokenEstimate / $this->originalTokenEstimate)) * 100,
            1,
        );
    }
}
