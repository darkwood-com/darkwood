<?php

declare(strict_types=1);

namespace App\Etl\Transformer;

use App\Etl\FlowContext;
use App\Etl\Row;
use App\Etl\Rows;
use App\Etl\Transformer;

/**
 * Normalizes emails and applies a deliberate CPU-heavy hash enrichment.
 * Profiling hotspot — visible as a distinct transformer frame in Blackfire.
 */
final class NormalizeOrderTransformer implements Transformer
{
    public function transform(Rows $rows, FlowContext $context): Rows
    {
        $transformed = [];

        foreach ($rows->all() as $row) {
            $amount = (float) $row->get('amount');

            if ($amount <= 0) {
                continue;
            }

            $email = strtolower(trim((string) $row->get('email')));
            $hash = (string) $row->get('id');

            for ($i = 0; $i < 1000; ++$i) {
                $hash = hash('sha256', $hash.$email.(string) $amount);
            }

            $transformed[] = $row
                ->with('email', $email)
                ->with('enrichment_hash', $hash);
        }

        return new Rows(...$transformed);
    }
}
