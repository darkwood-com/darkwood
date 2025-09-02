<?php

declare(strict_types=1);

namespace Flow\Examples\Model;

class HttpResponseData
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed>|null $parsedData
     */
    public function __construct(
        public int $id,
        public string $content,
        public int $statusCode,
        public array $headers = [],
        public ?array $parsedData = null
    ) {}
}
