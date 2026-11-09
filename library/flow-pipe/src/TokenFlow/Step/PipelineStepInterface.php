<?php

declare(strict_types=1);

namespace App\TokenFlow\Step;

use App\TokenFlow\PipelineContext;

interface PipelineStepInterface
{
    public static function name(): string;

    public function apply(PipelineContext $context): PipelineContext;

    public function displayName(): string;

    /**
     * @param array<string, scalar> $args
     */
    public function withArgs(array $args): self;
}
