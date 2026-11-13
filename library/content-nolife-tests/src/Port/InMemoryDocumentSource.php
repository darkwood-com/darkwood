<?php

declare(strict_types=1);

namespace App\Port;

final class InMemoryDocumentSource implements DocumentSource
{
    /**
     * @param array<string, string> $documents uri => html
     */
    public function __construct(private array $documents) {}

    public function fetch(string $uri): string
    {
        if (!isset($this->documents[$uri])) {
            throw new \RuntimeException(sprintf('Document not found: %s', $uri));
        }

        return $this->documents[$uri];
    }
}
