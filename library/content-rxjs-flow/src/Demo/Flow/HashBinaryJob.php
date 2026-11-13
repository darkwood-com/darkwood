<?php

declare(strict_types=1);

namespace App\Demo\Flow;

use App\Demo\Model\FetchedContent;
use App\Demo\Model\HashedContent;
use Flow\JobInterface;

/**
 * @implements JobInterface<FetchedContent, HashedContent>
 */
final class HashBinaryJob implements JobInterface
{
    public function __invoke($data): HashedContent
    {
        /** @var FetchedContent $data */
        if ($data->error !== null || $data->body === null) {
            return new HashedContent($data->url, null, null, $data->error ?? 'empty body');
        }

        return new HashedContent(
            $data->url,
            $data->body,
            hash('sha256', $data->body),
            null,
        );
    }
}
