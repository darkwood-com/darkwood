<?php

namespace Tests\Feature;

use App\Models\ExternalPublication;
use App\Models\PublicationAttempt;
use App\Support\PresentationCatalog;
use App\Support\PublicationRegistry;
use App\Support\PublicPublicationManifest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PublicPublicationManifestTest extends TestCase
{
    use RefreshDatabase;

    private function data(): array
    {
        return ['schema_version' => 1, 'publications' => [[
            'slide_id' => app(PresentationCatalog::class)->entries()[0]['id'],
            'provider' => 'speakerdeck', 'remote_id' => str_repeat('a', 32),
            'remote_url' => 'https://speakerdeck.com/matyo91/example',
            'source_hash' => str_repeat('b', 64), 'published_at' => null, 'last_synced_at' => null,
        ]]];
    }

    public function test_repository_public_manifest_has_only_approved_fields_and_canonical_identities(): void
    {
        $file = resource_path('data/publications.json');
        $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
        app(PublicPublicationManifest::class)->validate($data);
        foreach ($data['publications'] as $row) {
            $this->assertSame([], array_diff(array_keys($row), PublicPublicationManifest::FIELDS));
        }
        $this->assertStringNotContainsString('/Users/', file_get_contents($file));
        $this->assertStringNotContainsString('artifact_path', file_get_contents($file));
    }

    public function test_import_restores_public_mapping_on_a_fresh_deployment_and_is_idempotent(): void
    {
        Http::preventStrayRequests();
        $service = app(PublicPublicationManifest::class);
        $data = $this->data();
        $service->import($data);
        $p = ExternalPublication::firstOrFail();
        $id = $p->id;
        $p->update(['last_error' => 'fixture-private-detail']);
        $service->import($data);
        $this->assertDatabaseCount('presentations', 29);
        $this->assertDatabaseCount('external_publications', 1);
        $this->assertDatabaseCount('publication_attempts', 0);
        $this->assertSame($id, $p->fresh()->id);
        $this->assertSame('fixture-private-detail', $p->fresh()->last_error);
        $this->assertSame($data, $service->snapshot());
    }

    public function test_import_dry_run_rolls_back_catalog_and_mapping(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'public-manifest-');
        try {
            file_put_contents($file, json_encode($this->data()));
            $this->artisan('publications:manifest', ['--import' => $file, '--dry-run' => true])->assertSuccessful();
            $this->assertDatabaseCount('presentations', 0);
            $this->assertDatabaseCount('external_publications', 0);
        } finally {
            unlink($file);
        }
    }

    public function test_repository_manifest_rebuilds_all_public_associations_without_a_private_backup(): void
    {
        Http::preventStrayRequests();
        $originalStorage = $this->app->storagePath();
        $emptyStorage = sys_get_temp_dir().'/manifest-install-'.uniqid();
        $file = resource_path('data/publications.json');
        $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);

        try {
            $this->app->useStoragePath($emptyStorage);
            $this->assertDatabaseCount('presentations', 0);
            $this->assertDatabaseCount('external_publications', 0);
            $this->assertDirectoryDoesNotExist($emptyStorage);

            $this->artisan('publications:manifest', ['--import' => $file, '--dry-run' => true])->assertSuccessful();
            $this->assertDatabaseCount('presentations', 0);
            $this->assertDatabaseCount('external_publications', 0);

            $this->artisan('publications:manifest', ['--import' => $file])->assertSuccessful();
            $this->assertDatabaseCount('presentations', count(app(PresentationCatalog::class)->entries()));
            $this->assertDatabaseCount('external_publications', count($data['publications']));
            $this->assertSame($data, app(PublicPublicationManifest::class)->snapshot());
            $ids = ExternalPublication::orderBy('slide_id')->orderBy('provider')->pluck('id')->all();

            $this->artisan('publications:manifest', ['--import' => $file])->assertSuccessful();
            $this->assertSame($ids, ExternalPublication::orderBy('slide_id')->orderBy('provider')->pluck('id')->all());
            $this->assertSame($data, app(PublicPublicationManifest::class)->snapshot());
            $this->assertDatabaseCount('publication_attempts', 0);
            $this->assertDirectoryDoesNotExist($emptyStorage);
        } finally {
            $this->app->useStoragePath($originalStorage);
            File::deleteDirectory($emptyStorage);
        }
    }

    public function test_import_never_replaces_remote_identity_or_silently_overwrites_hash(): void
    {
        $service = app(PublicPublicationManifest::class);
        $data = $this->data();
        $service->import($data);
        $changed = $data;
        $changed['publications'][0]['source_hash'] = str_repeat('c', 64);
        try {
            $service->import($changed);
            $this->fail('Expected hash conflict.');
        } catch (\InvalidArgumentException) {
            $this->assertSame(str_repeat('b', 64), ExternalPublication::first()->source_hash);
        }
        $service->import($changed, true);
        $this->assertSame(str_repeat('c', 64), ExternalPublication::first()->source_hash);
        $changed['publications'][0]['remote_id'] = str_repeat('d', 32);
        $this->expectException(\InvalidArgumentException::class);
        $service->import($changed, true);
    }

    public function test_import_cannot_erase_an_unresolved_local_upload(): void
    {
        $service = app(PublicPublicationManifest::class);
        $data = $this->data();
        $service->import($data);
        PublicationAttempt::create(['external_publication_id' => ExternalPublication::first()->id,
            'source_hash' => str_repeat('c', 64), 'artifact_path' => '/private/fixture.pdf', 'operation' => 'replace', 'active_slot' => 1]);
        $this->expectException(\InvalidArgumentException::class);
        $service->import($data, true);
    }

    public function test_public_schema_rejects_private_fields_credentials_and_duplicate_remote_ids(): void
    {
        $data = $this->data();
        $cases = [];
        $bad = $data;
        $bad['publications'][0]['last_error'] = 'fixture-private-detail';
        $cases[] = $bad;
        $bad = $data;
        $bad['publications'][0]['published_at'] = '2026-02-31T00:00:00Z';
        $cases[] = $bad;
        $bad = $data;
        $bad['publications'][0]['remote_url'] = 'https://example:fixture@speakerdeck.com/matyo91/example';
        $cases[] = $bad;
        $bad = $data;
        $bad['publications'][] = $bad['publications'][0];
        $bad['publications'][1]['slide_id'] = app(PresentationCatalog::class)->entries()[1]['id'];
        $cases[] = $bad;
        foreach ($cases as $case) {
            try {
                app(PublicPublicationManifest::class)->validate($case);
                $this->fail('Unsafe public manifest accepted.');
            } catch (\InvalidArgumentException) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_diagnostics_omit_operational_errors_paths_and_attempts(): void
    {
        app(PublicPublicationManifest::class)->import($this->data());
        $p = ExternalPublication::first();
        $p->update(['last_error' => 'fixture-private-detail']);
        PublicationAttempt::create(['external_publication_id' => $p->id, 'source_hash' => str_repeat('c', 64), 'artifact_path' => '/private/fixture.pdf', 'operation' => 'replace', 'active_slot' => 1]);
        Artisan::call('publications:diagnose', ['--json' => true]);
        $output = Artisan::output();
        $this->assertStringNotContainsString('fixture-private-detail', $output);
        $this->assertStringNotContainsString('/private/fixture.pdf', $output);
        $data = json_decode($output, true);
        $this->assertSame(1, $data['active_attempt_count']);
        $this->assertSame(0, $data['orphan_count']);
        $this->assertCount(57, $data['missing']);
    }

    public function test_full_backup_stays_private_and_contains_recovery_material(): void
    {
        $root = sys_get_temp_dir().'/publication-private-'.uniqid();
        $original = $this->app->storagePath();
        try {
            $this->app->useStoragePath($root);
            app(PublicPublicationManifest::class)->import($this->data());
            ExternalPublication::first()->update(['last_error' => 'fixture-private-detail']);
            app(PublicationRegistry::class)->backup();
            $file = $root.'/app/private/publications/registry.json';
            $this->assertSame(0600, fileperms($file) & 0777);
            $this->assertSame(0700, fileperms(dirname($file)) & 0777);
            $this->assertStringContainsString('fixture-private-detail', file_get_contents($file));
            $this->assertArrayNotHasKey('last_error', app(PublicPublicationManifest::class)->snapshot()['publications'][0]);
        } finally {
            $this->app->useStoragePath($original);
            File::deleteDirectory($root);
        }
    }

    public function test_import_preserves_known_dates_and_never_erases_a_published_hash(): void
    {
        $service = app(PublicPublicationManifest::class);
        $data = $this->data();
        $service->import($data);
        $p = ExternalPublication::first();
        $p->update(['published_at' => '2026-01-01T00:00:00Z']);
        $service->import($data);
        $this->assertSame('2026-01-01', $p->fresh()->published_at->format('Y-m-d'));
        $data['publications'][0]['source_hash'] = null;
        $this->expectException(\InvalidArgumentException::class);
        $service->import($data, true);
    }
}
