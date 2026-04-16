<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Domain\Repository\RepositoryMetadata;

interface RepositoryCatalogPort
{
    /**
     * @return list<RepositoryMetadata>
     */
    public function all(): array;
}
