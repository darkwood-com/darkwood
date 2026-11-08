<?php

namespace Tests\Feature;

use App\Models\Presentation;
use App\Models\PublicationAttempt;
use App\Models\User;
use App\Support\AssistedPublication;
use App\Support\CanonicalRevision;
use App\Support\PublicationAssociations;
use App\Support\PublicationProviders;
use App\Support\SpeakerDeckProvider;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
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
        app(PublicationProviders::class)->validateIdentity('speakerdeck', 'title', 'https://speakerdeck.com/matyo91/title');
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

    public function test_provider_does_not_claim_an_upload_api_or_slideshare_replacement(): void
    {
        $providers = app(PublicationProviders::class);
        $this->assertSame('assisted', $providers->capabilities('speakerdeck')['create']);
        $this->assertSame('unavailable_platform_incident', $providers->capabilities('slideshare')['create']);
        $this->assertSame('unconfirmed', $providers->capabilities('slideshare')['replaceDocument']);
    }

    private function artifact(Presentation $deck): string
    {
        $directory = storage_path('app/publications/'.$deck->id.'/test-'.uniqid());
        mkdir($directory, 0700, true);
        $file = $directory.'/presentation.pdf';
        file_put_contents($file, '%PDF-test-fixture');
        file_put_contents($directory.'/pdf-validation.json', json_encode(['sha256' => hash_file('sha256', $file), 'pages' => $deck->slide_count]));
        file_put_contents($directory.'/manifest.json', json_encode(['id' => $deck->id, 'revision' => app(CanonicalRevision::class)->hash()]));
        $this->beforeApplicationDestroyed(fn () => File::deleteDirectory($directory));

        return $file;
    }

    public function test_prepare_dry_run_does_not_reserve_an_upload_or_change_the_registry(): void
    {
        $deck = $this->presentation();
        $this->artisan('publications:prepare', ['id' => $deck->id, 'provider' => 'speakerdeck', 'artifact' => $this->artifact($deck), '--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseCount('external_publications', 0);
        $this->assertDatabaseCount('publication_attempts', 0);
    }

    public function test_static_export_accepts_extra_pages_only_with_a_complete_verified_page_map(): void
    {
        $deck = $this->presentation();
        $file = $this->artifact($deck);
        $manifestFile = dirname($file).'/manifest.json';
        $manifest = json_decode(file_get_contents($manifestFile), true);
        $manifest['static_projection'] = true;
        file_put_contents($manifestFile, json_encode($manifest));
        $map = [];
        foreach (range(1, 20) as $slide) {
            $map[] = ['page' => count($map) + 1, 'slide' => $slide];
            if ($slide === 7) {
                $map[] = ['page' => count($map) + 1, 'slide' => $slide];
            }
        }
        $mapFile = dirname($file).'/page-map.json';
        file_put_contents($mapFile, json_encode($map));
        file_put_contents(dirname($file).'/pdf-validation.json', json_encode(['sha256' => hash_file('sha256', $file), 'pages' => 21, 'canonical_slides' => 20, 'page_map_sha256' => hash_file('sha256', $mapFile)]));
        $attempt = app(AssistedPublication::class)->prepare($deck, 'speakerdeck', $file);
        $this->assertInstanceOf(PublicationAttempt::class, $attempt);
        file_put_contents($mapFile, '[]');
        $this->expectException(InvalidArgumentException::class);
        app(AssistedPublication::class)->prepare($deck, 'slideshare', $file);
    }

    public function test_interrupted_attempt_blocks_a_second_upload(): void
    {
        $deck = $this->presentation();
        $artifact = $this->artifact($deck);
        $service = app(AssistedPublication::class);
        $attempt = $service->prepare($deck, 'speakerdeck', $artifact);
        $this->assertInstanceOf(PublicationAttempt::class, $attempt);
        $this->assertSame(1, $attempt->active_slot);
        $this->expectException(InvalidArgumentException::class);
        $service->prepare($deck, 'speakerdeck', $artifact);
    }

    public function test_identical_pdf_is_skipped_and_changed_pdf_does_not_create_a_duplicate(): void
    {
        $deck = $this->presentation();
        $artifact = $this->artifact($deck);
        $service = app(AssistedPublication::class);
        $attempt = $service->prepare($deck, 'speakerdeck', $artifact);
        $id = str_repeat('a', 32);
        $published = $service->complete($attempt, $id, 'https://speakerdeck.com/matyo91/example', true, true);
        $this->assertSame('published', $published->status);
        $this->assertSame($published->id, $service->prepare($deck, 'speakerdeck', $artifact)->id);
        $published->update(['source_hash' => str_repeat('b', 64)]);
        $changed = $service->prepare($deck, 'speakerdeck', $artifact);
        $this->assertSame('update_available', $changed->status);
        $this->assertSame(1, PublicationAttempt::count());
        $this->assertSame($id, $changed->remote_id);
    }

    public function test_incomplete_or_unverified_results_do_not_close_the_attempt(): void
    {
        $deck = $this->presentation();
        $attempt = app(AssistedPublication::class)->prepare($deck, 'speakerdeck', $this->artifact($deck));
        try {
            app(AssistedPublication::class)->complete($attempt, 'slug', 'https://speakerdeck.com/matyo91/example', true, true);
            $this->fail('Invalid remote ID accepted');
        } catch (InvalidArgumentException) {
            $this->assertSame(1, $attempt->fresh()->active_slot);
            $this->assertNull($deck->publications()->first()->remote_id);
        }
        $this->expectException(InvalidArgumentException::class);
        app(AssistedPublication::class)->complete($attempt, str_repeat('a', 32), 'https://speakerdeck.com/matyo91/example', false, true);
    }

    public function test_stale_artifact_is_rejected(): void
    {
        $deck = $this->presentation();
        $artifact = $this->artifact($deck);
        file_put_contents(dirname($artifact).'/manifest.json', json_encode(['id' => $deck->id, 'revision' => 'stale']));
        $this->expectException(InvalidArgumentException::class);
        app(AssistedPublication::class)->prepare($deck, 'speakerdeck', $artifact);
    }

    public function test_oembed_reads_real_player_identity_without_using_the_slug_as_id(): void
    {
        Http::preventStrayRequests();
        $id = str_repeat('a', 32);
        Http::fake(['speakerdeck.com/oembed.json*' => Http::response(['provider_name' => 'Speaker Deck', 'author_url' => 'https://speakerdeck.com/matyo91', 'title' => 'Example', 'html' => '<iframe src="https://speakerdeck.com/player/'.$id.'"></iframe>'])]);
        $result = app(SpeakerDeckProvider::class)->getPublication('https://speakerdeck.com/matyo91/changed-title', $id);
        $this->assertSame($id, $result['remote_id']);
        $this->assertSame('public_oembed_identity_only', $result['verification']);
    }

    public function test_oembed_rejects_incomplete_payloads(): void
    {
        Http::preventStrayRequests();
        Http::fake(['speakerdeck.com/oembed.json*' => Http::response(['title' => 'Incomplete'])]);
        $this->expectException(\RuntimeException::class);
        app(SpeakerDeckProvider::class)->getPublication('https://speakerdeck.com/matyo91/example', str_repeat('a', 32));
    }

    public function test_oembed_authentication_errors_are_not_treated_as_publications(): void
    {
        Http::preventStrayRequests();
        Http::fake(['speakerdeck.com/oembed.json*' => Http::response([], 401)]);
        $this->expectException(\RuntimeException::class);
        app(SpeakerDeckProvider::class)->getPublication('https://speakerdeck.com/matyo91/example', str_repeat('a', 32));
    }

    public function test_operator_screen_is_local_only_and_does_not_change_the_reader(): void
    {
        $this->get('/publications')->assertNotFound();
        config(['publications.local_operator_ui' => true]);
        $this->get('/publications')->assertNotFound();
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->get('/publications')->assertNotFound();
        config(['publications.operator_ids' => [(string) $user->id]]);
        $this->get('/publications')->assertOk()->assertSee('Publications Slidewire');
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])->get('/publications')->assertNotFound();
    }

    public function test_versioned_catalog_has_29_distinct_stable_uuid_identities(): void
    {
        $rows = json_decode(file_get_contents(resource_path('presentations.json')), true);
        $this->assertCount(29, $rows);
        $this->assertCount(29, array_unique(array_column($rows, 'id')));
        foreach ($rows as $row) {
            $this->assertTrue(Str::isUuid($row['id']));
        }
    }

    public function test_database_allows_only_one_active_attempt_per_publication(): void
    {
        $publication = $this->presentation()->publications()->create(['provider' => 'speakerdeck']);
        $values = ['external_publication_id' => $publication->id, 'source_hash' => str_repeat('a', 64), 'artifact_path' => '/test', 'operation' => 'create', 'active_slot' => 1];
        PublicationAttempt::create($values);
        $this->expectException(QueryException::class);
        PublicationAttempt::create($values);
    }

    public function test_upload_error_stays_reserved_until_manually_reconciled(): void
    {
        $deck = $this->presentation();
        $attempt = app(AssistedPublication::class)->prepare($deck, 'speakerdeck', $this->artifact($deck));
        $this->artisan('publications:reconcile', ['attempt' => $attempt->id, '--error' => 'authentication_failed'])->assertSuccessful();
        $this->assertSame(1, $attempt->fresh()->active_slot);
        $this->assertSame('authentication_failed', $attempt->fresh()->last_error);
        $this->artisan('publications:reconcile', ['attempt' => $attempt->id, '--error' => 'upload_failed', '--confirmed-no-remote-publication' => true])->assertSuccessful();
        $this->assertNull($attempt->fresh()->active_slot);
        $this->assertSame('failed_confirmed_no_publication', $attempt->fresh()->status);
    }
}
