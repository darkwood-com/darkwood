<?php

namespace App\Support;

use InvalidArgumentException;

/** Official web workflows only. No write API is claimed or invoked. */
class PublicationProviders
{
    public function capabilities(string $provider): array
    {
        return match ($provider) {
            'speakerdeck' => app(SpeakerDeckProvider::class)->capabilities(),
            'slideshare' => app(SlideShareProvider::class)->capabilities(),
            default => throw new InvalidArgumentException('Unknown provider.'),
        };
    }

    public function validateIdentity(string $provider, string $id, string $url): void
    {
        $this->capabilities($provider);
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || isset($parts['query']) || isset($parts['fragment'])) {
            throw new InvalidArgumentException('Use the canonical HTTPS public URL.');
        }
        $valid = $provider === 'speakerdeck'
            ? ($parts['host'] ?? '') === 'speakerdeck.com' && preg_match('/^\/matyo91\/[a-z0-9-]+$/D', $parts['path'] ?? '') && preg_match('/^[a-f0-9]{32}$/D', $id)
            : in_array($parts['host'] ?? '', ['fr.slideshare.net', 'www.slideshare.net'], true) && preg_match('/^\/slideshow\/[^\/]+\/'.preg_quote($id, '/').'$/D', $parts['path'] ?? '') && preg_match('/^[1-9][0-9]+$/D', $id);
        if (! $valid) {
            throw new InvalidArgumentException('Remote identity is invalid; a slug is not an ID.');
        }
    }
}
