<?php

declare(strict_types=1);

namespace Flow\Examples\Model;

class HttpResponseData
{
    public function __construct(
        public int $id,
        public string $content,
        public int $statusCode,
        public array $headers = [],
        public ?array $parsedData = null
    ) {}
}
