<?php

declare(strict_types=1);

namespace App\NoLifeModels\Domain\Model;

/**
 * A model tag reported by a local runtime (e.g. Ollama).
 */
final readonly class LocalModel
{
    public function __construct(
        public string $name,
        public ?int $sizeBytes,
        public ?string $modifiedAt,
        public ?string $digest,
    ) {
    }
}
