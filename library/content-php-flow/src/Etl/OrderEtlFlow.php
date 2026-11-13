<?php

declare(strict_types=1);

namespace App\Etl;

use App\Etl\Extractor\OrderBatchExtractor;
use App\Etl\Loader\JsonOrderLoader;
use App\Etl\Transformer\NormalizeOrderTransformer;
use Generator;

/**
 * Order ETL flow — configures extract / transform / load like flow-php's
 * {@see \Flow\ETL\Flow::read()} → {@see \Flow\ETL\DataFrame::collect()} → {@see \Flow\ETL\DataFrame::write()}.
 */
final class OrderEtlFlow implements FlowInterface
{
    private function __construct(
        private readonly Extractor $extractor,
        private readonly Transformer $transformer,
        private readonly Loader $loader,
    ) {}

    public static function create(): self
    {
        return new self(
            new OrderBatchExtractor(),
            new NormalizeOrderTransformer(),
            new JsonOrderLoader(),
        );
    }

    public function extractor(): Extractor
    {
        return $this->extractor;
    }

    public function transformer(): Transformer
    {
        return $this->transformer;
    }

    public function loader(): Loader
    {
        return $this->loader;
    }

    public function pipeline(): Pipeline
    {
        return new Pipeline($this->extractor, $this->transformer, $this->loader);
    }

    /**
     * @return Generator<Rows>
     */
    public function run(FlowContext $context): Generator
    {
        yield from $this->pipeline()->process($context);
    }
}
