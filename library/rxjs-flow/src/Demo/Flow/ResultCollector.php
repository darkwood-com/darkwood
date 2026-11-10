<?php

declare(strict_types=1);

namespace App\Demo\Flow;

use App\Demo\Model\ImageResult;

final class ResultCollector
{
    /** @var list<ImageResult> */
    private array $results = [];

    public function add(ImageResult $result): void
    {
        $this->results[] = $result;
    }

    /**
     * @return list<ImageResult>
     */
    public function all(): array
    {
        return $this->results;
    }
}
