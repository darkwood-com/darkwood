<?php

declare(strict_types=1);

namespace App\Etl\Extractor;

use App\Etl\Extractor;
use App\Etl\FlowContext;
use App\Etl\Row;
use App\Etl\Rows;
use Generator;

/**
 * Extracts order rows from the batch carried in {@see FlowContext}.
 * Mirrors flow-php extractors yielding {@see \Flow\ETL\Rows} batches.
 */
final class OrderBatchExtractor implements Extractor
{
    public function extract(FlowContext $context): Generator
    {
        if (null === $context->batch) {
            yield new Rows();

            return;
        }

        $rows = array_map(
            static fn ($order): Row => Row::fromOrderRow($order),
            $context->batch->rows
        );

        yield new Rows(...$rows);
    }
}
