<?php

declare(strict_types=1);

namespace App\Etl;

use App\Model\OrderBatch;

/**
 * Execution context — simplified {@see \Flow\ETL\FlowContext}.
 */
final class FlowContext
{
    public function __construct(
        public readonly string $outputDir,
        public readonly ?OrderBatch $batch = null,
        public ?string $outputFile = null,
    ) {}
}
