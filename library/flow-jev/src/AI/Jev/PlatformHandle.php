<?php

declare(strict_types=1);

namespace App\AI\Jev;

use Symfony\AI\Platform\PlatformInterface;

final readonly class PlatformHandle
{
    public function __construct(
        public PlatformInterface $platform,
        public string $source,
        public string $cassettePath,
    ) {
    }
}
