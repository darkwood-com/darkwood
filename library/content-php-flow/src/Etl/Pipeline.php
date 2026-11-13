<?php

declare(strict_types=1);

namespace App\Etl;

use Generator;

/**
 * Internal pipeline — simplified {@see \Flow\ETL\Pipeline}.
 */
final class Pipeline
{
    public function __construct(
        private readonly Extractor $extractor,
        private readonly Transformer $transformer,
        private readonly Loader $loader,
    ) {}

    /**
     * @return Generator<Rows>
     */
    public function process(FlowContext $context): Generator
    {
        $generator = $this->extractor->extract($context);

        foreach ($generator as $rows) {
            $rows = $this->transformer->transform($rows, $context);

            if ($rows->count() > 0) {
                $this->loader->load($rows, $context);
            }

            yield $rows;
        }
    }

    public function extract(FlowContext $context): Rows
    {
        foreach ($this->extractor->extract($context) as $rows) {
            return $rows;
        }

        return new Rows();
    }

    public function transform(Rows $rows, FlowContext $context): Rows
    {
        return $this->transformer->transform($rows, $context);
    }

    public function load(Rows $rows, FlowContext $context): void
    {
        $this->loader->load($rows, $context);
    }
}
