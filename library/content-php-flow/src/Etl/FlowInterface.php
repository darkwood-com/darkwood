<?php

declare(strict_types=1);

namespace App\Etl;

use Generator;

/**
 * ETL flow contract — inspired by flow-php/etl pipeline execution
 * ({@see \Flow\ETL\Pipeline::process()} and {@see \Flow\ETL\DataFrame::run()}).
 */
interface FlowInterface
{
    public function extractor(): Extractor;

    public function transformer(): Transformer;

    public function loader(): Loader;

    /**
     * Run the full ETL pipeline (extract → transform → load).
     *
     * @return Generator<Rows>
     */
    public function run(FlowContext $context): Generator;
}
