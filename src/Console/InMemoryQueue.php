<?php

declare(strict_types=1);

namespace App\Console;

use App\Model\OrderBatch;
use App\Model\OrderRow;

final class InMemoryQueue
{
    /** @var list<OrderBatch> */
    private array $messages = [];

    private int $position = 0;

    public function __construct(string $csvPath, int $chunkSize = 10)
    {
        if (!is_file($csvPath)) {
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (false === $handle) {
            return;
        }

        fgetcsv($handle, 0, ',', '"', ''); // skip header
        $chunk = [];
        $batchIndex = 0;

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (3 !== \count($row)) {
                continue;
            }

            $chunk[] = new OrderRow($row[0], $row[1], (float) $row[2]);

            if (\count($chunk) >= $chunkSize) {
                $this->messages[] = new OrderBatch(sprintf('batch-%03d', ++$batchIndex), $chunk);
                $chunk = [];
            }
        }

        if ([] !== $chunk) {
            $this->messages[] = new OrderBatch(sprintf('batch-%03d', ++$batchIndex), $chunk);
        }

        fclose($handle);
    }

    public function next(): ?OrderBatch
    {
        if ([] === $this->messages) {
            return null;
        }

        $message = $this->messages[$this->position];
        $this->position = ($this->position + 1) % \count($this->messages);

        return $message;
    }
}
