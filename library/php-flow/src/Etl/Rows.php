<?php

declare(strict_types=1);

namespace App\Etl;

use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Batch of rows — inspired by {@see \Flow\ETL\Rows}.
 *
 * @implements IteratorAggregate<int, Row>
 */
final class Rows implements Countable, IteratorAggregate
{
    /** @var list<Row> */
    private array $rows;

    public function __construct(Row ...$rows)
    {
        $this->rows = array_values($rows);
    }

    public function count(): int
    {
        return \count($this->rows);
    }

    public function getIterator(): Traversable
    {
        yield from $this->rows;
    }

    /**
     * @return list<Row>
     */
    public function all(): array
    {
        return $this->rows;
    }

    public function map(callable $callback): self
    {
        return new self(...array_map($callback, $this->rows));
    }
}
