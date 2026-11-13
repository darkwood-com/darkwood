<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\DocumentRequest;
use App\Model\RawDocument;
use App\Port\DocumentSource;
use Flow\JobInterface;

/**
 * @implements JobInterface<DocumentRequest, RawDocument>
 */
final class FetchJob implements JobInterface
{
    public function __construct(private DocumentSource $source) {}

    public function __invoke($data): RawDocument
    {
        assert($data instanceof DocumentRequest);

        return new RawDocument($data->uri, $this->source->fetch($data->uri));
    }
}
