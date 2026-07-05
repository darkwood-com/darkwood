<?php

declare(strict_types=1);

namespace App\Etl;

/**
 * Simplified adaptation of flow-php/etl {@see \Flow\ETL\Loader}.
 */
interface Loader
{
    public function load(Rows $rows, FlowContext $context): void;
}
