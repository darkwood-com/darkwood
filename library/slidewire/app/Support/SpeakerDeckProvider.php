<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpeakerDeckProvider
{
    public function capabilities(): array
    {
        return ['create' => 'assisted', 'updateMetadata' => 'assisted', 'replaceDocument' => 'assisted_confirmation_required', 'getPublication' => 'oembed_by_url', 'delete' => 'not_implemented', 'upload_url' => 'https://speakerdeck.com/new'];
    }

    /** Official oEmbed only. It retrieves identity by URL, not a PDF or a content hash. */
    public function getPublication(string $url, string $expectedId): array
    {
        app(PublicationProviders::class)->validateIdentity('speakerdeck', $expectedId, $url);
        $response = Http::timeout(20)->withoutRedirecting()->get('https://speakerdeck.com/oembed.json', ['url' => $url]);
        $response->throw();
        $payload = $response->json();
        if (! is_array($payload) || ($payload['author_url'] ?? '') !== 'https://speakerdeck.com/matyo91' || ($payload['provider_name'] ?? '') !== 'Speaker Deck' || ! preg_match('~https://speakerdeck\.com/player/([a-f0-9]{32})(?:["?])~', $payload['html'] ?? '', $matches) || $matches[1] !== $expectedId || empty($payload['title'])) {
            throw new RuntimeException('Incomplete oEmbed response or remote identity/account mismatch.');
        }

        return ['remote_id' => $matches[1], 'remote_url' => $url, 'title' => $payload['title'], 'verification' => 'public_oembed_identity_only'];
    }
}
