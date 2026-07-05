<?php

declare(strict_types=1);

namespace App\Etl\Loader;

use App\Etl\FlowContext;
use App\Etl\Loader;
use App\Etl\Rows;

/**
 * Writes transformed rows as JSON — mirrors flow-php loader stage.
 */
final class JsonOrderLoader implements Loader
{
    public function load(Rows $rows, FlowContext $context): void
    {
        $payload = array_map(
            static fn ($row) => $row->entries,
            $rows->all()
        );

        $batchId = $context->batch?->id ?? 'unknown';
        $file = $context->outputDir.'/orders-'.$batchId.'.json';

        if (!is_dir($context->outputDir)) {
            mkdir($context->outputDir, 0777, true);
        }

        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT));
        $context->outputFile = $file;
    }
}
