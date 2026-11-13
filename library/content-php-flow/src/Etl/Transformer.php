<?php

declare(strict_types=1);

namespace App\Etl;

/**
 * Simplified adaptation of flow-php/etl {@see \Flow\ETL\Transformer}.
 */
interface Transformer
{
    public function transform(Rows $rows, FlowContext $context): Rows;
}
