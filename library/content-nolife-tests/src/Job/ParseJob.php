<?php

declare(strict_types=1);

namespace App\Job;

use App\Model\ParsedDocument;
use App\Model\RawDocument;
use Flow\JobInterface;

/**
 * @implements JobInterface<RawDocument, ParsedDocument>
 */
final class ParseJob implements JobInterface
{
    public function __invoke($data): ParsedDocument
    {
        assert($data instanceof RawDocument);

        $title = 'Untitled';
        if (preg_match('/<title>(.*?)<\/title>/is', $data->html, $matches) === 1) {
            $title = trim(html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        $body = $data->html;
        if (preg_match('/<body\b[^>]*>(.*?)<\/body>/is', $data->html, $matches) === 1) {
            $body = $matches[1];
        }

        $body = preg_replace('/<(br|p|div|h[1-6]|li|tr)\b[^>]*>/i', ' ', $body) ?? $body;
        $body = trim(html_entity_decode(strip_tags($body), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $body = preg_replace('/\s+/u', ' ', $body) ?? $body;

        return new ParsedDocument($data->uri, $title, $body);
    }
}
