<?php

namespace App\Integration\Media;

final readonly class MediaGenerationResult
{
    /**
     * @param list<string> $artifacts
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public string $requestId,
        public string $status,
        public array $artifacts = [],
        public array $metadata = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return ['request_id' => $this->requestId, 'status' => $this->status, 'artifacts' => $this->artifacts, 'metadata' => $this->metadata];
    }
}
