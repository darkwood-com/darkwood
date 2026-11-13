<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Repository\RepositoryDocument;
use App\Domain\Repository\RepositoryMetadata;

final class RepositoryDocumentBuilder
{
    /**
     * @param list<string> $fragments
     */
    public function build(RepositoryMetadata $metadata, array $fragments): RepositoryDocument
    {
        return new RepositoryDocument(
            $metadata->id,
            $metadata->name,
            $metadata->fullName,
            $metadata->ownerLogin,
            $metadata->description,
            $metadata->topics,
            $fragments,
        );
    }
}
