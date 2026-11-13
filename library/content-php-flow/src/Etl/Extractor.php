<?php

declare(strict_types=1);

namespace App\Etl;

use Generator;

/**
 * Simplified adaptation of flow-php/etl {@see \Flow\ETL\Extractor}.
 */
interface Extractor
{
    /**
     * @return Generator<Rows>
     */
    public function extract(FlowContext $context): Generator;
}
