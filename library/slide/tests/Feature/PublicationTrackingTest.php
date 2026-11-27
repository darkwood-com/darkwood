<?php

namespace Tests\Feature;

use App\Support\PresentationCatalog;
use App\Support\RemotePublicationIdentity;
use InvalidArgumentException;
use Tests\TestCase;

class PublicationTrackingTest extends TestCase
{
    public function test_slug_is_not_accepted_as_remote_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(RemotePublicationIdentity::class)->validate('speakerdeck', 'title', 'https://speakerdeck.com/matyo91/title');
    }

    public function test_versioned_catalog_has_distinct_stable_uuid_identities(): void
    {
        $catalog = app(PresentationCatalog::class);
        $entries = $catalog->entries();
        $this->assertNotEmpty($entries);
        $this->assertSame(count($entries), count(array_unique(array_column($entries, 'id'))));
        foreach ($entries as $entry) {
            $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $entry['id']);
            $this->assertSame($entry, $catalog->find($entry['id']));
        }
    }
}
