<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Repository\RepositoryMetadata;

interface RepositoryContentReaderPort
{
    /**
     * @return list<string>
     */
    public function readTextMaterial(RepositoryMetadata $metadata): array;
}
