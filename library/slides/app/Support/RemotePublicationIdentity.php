<?php

namespace App\Support;

use InvalidArgumentException;

/** Public data-integrity boundary. Provider operations live in Slides Core. */
class RemotePublicationIdentity
{
    public function validate(string $provider, string $id, string $url): void
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Use the canonical HTTPS public URL.');
        }
        $valid = match ($provider) {
            'speakerdeck' => ($parts['host'] ?? '') === 'speakerdeck.com'
                && preg_match('/^\/matyo91\/[a-z0-9-]+$/D', $parts['path'] ?? '')
                && preg_match('/^[a-f0-9]{32}$/D', $id),
            'slideshare' => in_array($parts['host'] ?? '', ['fr.slideshare.net', 'www.slideshare.net'], true)
                && preg_match('/^\/slideshow\/[^\/]+\/'.preg_quote($id, '/').'$/D', $parts['path'] ?? '')
                && preg_match('/^[1-9][0-9]+$/D', $id),
            default => false,
        };
        if (! $valid) {
            throw new InvalidArgumentException('Remote identity is invalid; a slug is not an ID.');
        }
    }
}
