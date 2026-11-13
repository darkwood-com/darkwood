<?php

namespace App\Integration\Ai\Structured;

final readonly class SeoGeoRecommendation
{
    /**
     * @param list<string> $keywords
     */
    public function __construct(
        public array $keywords,
        public string $metadataDescription,
    ) {
    }
}
