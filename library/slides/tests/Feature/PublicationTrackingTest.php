<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Support\PresentationCatalog;
use App\Support\PublicationAssociations;
use App\Support\RemotePublicationIdentity;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PublicationTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function presentation(): Presentation
    {
        return Presentation::create(['id' => 'ba7ee622-d89f-4be9-b0d9-e37c14f16f75', 'source_slug' => 'original', 'title' => 'Original', 'slide_count' => 20]);
    }

    public function test_association_survives_title_and_slug_changes_without_claiming_pdf_equality(): void
    {
        $deck = $this->presentation();
        $service = app(PublicationAssociations::class);
        $publication = $service->associate($deck, 'slideshare', '245084564', 'https://fr.slideshare.net/slideshow/railway-flow-based-programming/245084564', true);
        $deck->update(['source_slug' => 'renamed', 'title' => 'Renamed']);
        $this->assertSame($deck->id, $publication->fresh()->presentation->id);
        $this->assertNull($publication->source_hash);
        $this->assertNull($publication->last_synced_at);
        $again = $service->associate($deck, 'slideshare', '245084564', $publication->remote_url, true);
        $this->assertSame($publication->id, $again->id);
        $this->assertSame(1, $deck->publications()->count());
    }

    public function test_unconfirmed_correspondence_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(PublicationAssociations::class)->associate($this->presentation(), 'slideshare', '245084564', 'https://fr.slideshare.net/slideshow/example/245084564', false);
    }

    public function test_slug_is_not_accepted_as_remote_id(): void
    {
        $this->expectException(InvalidArgumentException::class);
        app(RemotePublicationIdentity::class)->validate('speakerdeck', 'title', 'https://speakerdeck.com/matyo91/title');
    }

    public function test_database_prevents_duplicate_provider_associations(): void
    {
        $deck = $this->presentation();
        $deck->publications()->create(['provider' => 'speakerdeck']);
        $this->expectException(QueryException::class);
        $deck->publications()->create(['provider' => 'speakerdeck']);
    }

    public function test_database_prevents_associating_one_remote_deck_twice(): void
    {
        $deck = $this->presentation();
        $deck->publications()->create(['provider' => 'slideshare', 'remote_id' => '123456']);
        $other = Presentation::create(['id' => 'cd7ee622-d89f-4be9-b0d9-e37c14f16f75', 'source_slug' => 'other', 'title' => 'Other', 'slide_count' => 2]);
        $this->expectException(QueryException::class);
        $other->publications()->create(['provider' => 'slideshare', 'remote_id' => '123456']);
    }

    public function test_versioned_catalog_has_distinct_stable_uuid_identities(): void
    {
        $entries = app(PresentationCatalog::class)->entries();
        $this->assertNotEmpty($entries);
        $this->assertSame(count($entries), count(array_unique(array_column($entries, 'id'))));
        foreach (array_column($entries, 'id') as $id) {
            $this->assertMatchesRegularExpression('/^[a-f0-9-]{36}$/', $id);
        }
    }
}
