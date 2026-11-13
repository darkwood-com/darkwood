<?php

namespace App\Integration\Media;

final readonly class MediaGenerationRequest
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $kind,
        public string $runId,
        public array $payload,
        public array $context = [],
    ) {
    }
}
