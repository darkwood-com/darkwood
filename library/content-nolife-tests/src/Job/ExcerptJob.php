<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\ExcerptDraft;
use App\Model\ParsedDocument;
use Flow\JobInterface;

/**
 * @implements JobInterface<ParsedDocument, ExcerptDraft>
 */
final class ExcerptJob implements JobInterface
{
    public function __construct(private int $maxLength = 120) {}

    public function __invoke($data): ExcerptDraft
    {
        assert($data instanceof ParsedDocument);

        $text = $data->body;
        if (mb_strlen($text) <= $this->maxLength) {
            return new ExcerptDraft($data->uri, $data->title, $text);
        }

        $cut = mb_substr($text, 0, $this->maxLength);
        $lastSpace = mb_strrpos($cut, ' ');
        if ($lastSpace !== false && $lastSpace > (int) ($this->maxLength * 0.5)) {
            $cut = mb_substr($cut, 0, $lastSpace);
        }

        return new ExcerptDraft($data->uri, $data->title, rtrim($cut) . '…');
    }
}
