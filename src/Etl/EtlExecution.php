<?php

declare(strict_types=1);

namespace App\Etl;

use App\Model\OrderBatch;

/**
 * Holds ETL state between darkwood/flow orchestration stages.
 */
final class EtlExecution
{
    public function __construct(
        public readonly OrderBatch $batch,
        public readonly FlowInterface $flow,
        public readonly FlowContext $context,
        public ?Rows $rows = null,
        public int $loadedCount = 0,
    ) {}
}
