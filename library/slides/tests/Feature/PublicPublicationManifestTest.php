<?php

namespace Tests\Feature;

use App\Support\PresentationCatalog;
use App\Support\PublicPublicationManifest;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;
use Tests\TestCase;

class PublicPublicationManifestTest extends TestCase
{
    private string $directory;
    private string $manifestPath;
    private PublicPublicationManifest $manifest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/slides-manifest-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        $this->manifestPath = $this->directory.'/publications.json';
        file_put_contents($this->manifestPath, '{"schema_version":1,"publications":[]}');
        $this->manifest = new PublicPublicationManifest($this->manifestPath);
        $this->app->instance(PublicPublicationManifest::class, $this->manifest);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);
        parent::tearDown();
    }

    private function data(): array
    {
        return ['schema_version' => 1, 'publications' => [[
            'slide_id' => app(PresentationCatalog::class)->entries()[0]['id'],
            'provider' => 'speakerdeck',
            'remote_id' => str_repeat('a', 32),
            'remote_url' => 'https://speakerdeck.com/matyo91/example',
            'source_hash' => str_repeat('b', 64),
            'published_at' => null,
            'last_synced_at' => null,
        ]]];
    }

    public function test_repository_public_manifest_has_only_approved_fields_and_canonical_identities(): void
    {
        $file = resource_path('data/publications.json');
        $data = $this->manifest->read($file);
        foreach ($data['publications'] as $row) {
            $this->assertSame([], array_diff(array_keys($row), PublicPublicationManifest::FIELDS));
        }
        $this->assertStringNotContainsString('/Users/', file_get_contents($file));
        $this->assertStringNotContainsString('artifact_path', file_get_contents($file));
    }

    public function test_import_and_export_are_idempotent_without_database_or_network(): void
    {
        Http::preventStrayRequests();
        $data = $this->manifest->read(resource_path('data/publications.json'));

        $this->artisan('publications:manifest', ['--import' => resource_path('data/publications.json')])->assertSuccessful();
        $this->assertSame($data, $this->manifest->snapshot());
        $inode = fileinode($this->manifestPath);

        $this->manifest->import($data);
        $this->artisan('publications:manifest', ['--export' => true])->assertSuccessful();
        clearstatcache(true, $this->manifestPath);
        $this->assertSame($inode, fileinode($this->manifestPath));
        $this->assertSame($data, $this->manifest->snapshot());
    }

    public function test_dry_run_previews_changes_without_writing_any_files(): void
    {
        $file = $this->directory.'/incoming.json';
        file_put_contents($file, json_encode($this->data(), JSON_THROW_ON_ERROR));
        $before = file_get_contents($this->manifestPath);

        $this->artisan('publications:manifest', ['--import' => $file, '--dry-run' => true])->assertSuccessful();
        $this->assertSame($before, file_get_contents($this->manifestPath));
        $this->assertFileDoesNotExist($this->directory.'/.publications.json.lock');
    }

    public function test_association_requires_confirmation_and_never_claims_pdf_equality(): void
    {
        $row = $this->data()['publications'][0];
        try {
            $this->manifest->associate($row['slide_id'], $row['provider'], $row['remote_id'], $row['remote_url'], false);
            $this->fail('Expected explicit confirmation.');
        } catch (InvalidArgumentException) {
            $this->assertSame([], $this->manifest->snapshot()['publications']);
        }

        for ($i = 0; $i < 2; ++$i) {
            $saved = $this->manifest->associate($row['slide_id'], $row['provider'], $row['remote_id'], $row['remote_url'], true);
            $this->assertNull($saved['source_hash']);
            $this->assertNull($saved['last_synced_at']);
        }
        $this->assertCount(1, $this->manifest->snapshot()['publications']);
    }

    public function test_import_requires_review_for_metadata_changes_and_cannot_replace_an_identity(): void
    {
        $original = $this->data();
        $original['publications'][0]['published_at'] = '2026-01-01T00:00:00Z';
        $this->manifest->import($original);
        $change = $this->data();
        $change['publications'][0]['source_hash'] = str_repeat('c', 64);

        try {
            $this->manifest->import($change);
            $this->fail('Expected hash conflict.');
        } catch (InvalidArgumentException) {
            $this->assertSame($original, $this->manifest->snapshot());
        }

        $this->manifest->import($change, true);
        $saved = $this->manifest->snapshot();
        $this->assertSame('2026-01-01T00:00:00Z', $saved['publications'][0]['published_at']);
        foreach (['source_hash' => null, 'remote_id' => str_repeat('d', 32)] as $key => $value) {
            $conflict = $change;
            $conflict['publications'][0][$key] = $value;
            try {
                $this->manifest->import($conflict, true);
                $this->fail('Expected identity or hash conflict.');
            } catch (InvalidArgumentException) {
                $this->assertSame($saved, $this->manifest->snapshot());
            }
        }
    }

    public function test_invalid_publication_fields_and_duplicates_are_rejected(): void
    {
        $data = $this->data();
        $cases = [];
        foreach ([
            'last_error' => 'private detail',
            'published_at' => '2026-02-31T00:00:00Z',
            'remote_url' => 'https://name:secret@speakerdeck.com/matyo91/example',
            'slide_id' => '00000000-0000-4000-8000-000000000000',
            'provider' => 'unknown',
            'remote_id' => null,
        ] as $key => $value) {
            $bad = $data;
            $bad['publications'][0][$key] = $value;
            $cases[] = $bad;
        }
        $bad = $data;
        $bad['publications'][] = $bad['publications'][0];
        $cases[] = $bad;
        $bad['publications'][1]['slide_id'] = app(PresentationCatalog::class)->entries()[1]['id'];
        $cases[] = $bad;
        foreach ($cases as $case) {
            try {
                $this->manifest->import($case);
                $this->fail('Unsafe public manifest accepted.');
            } catch (InvalidArgumentException) {
                $this->assertSame([], $this->manifest->snapshot()['publications']);
            }
        }
    }

    public function test_import_cannot_claim_another_presentations_remote_id(): void
    {
        $data = $this->data();
        $this->manifest->import($data);
        $data['publications'][0]['slide_id'] = app(PresentationCatalog::class)->entries()[1]['id'];

        $this->expectException(InvalidArgumentException::class);
        $this->manifest->import($data);
    }

    public function test_object_is_not_accepted_in_place_of_the_publications_list(): void
    {
        file_put_contents($this->manifestPath, '{"schema_version":1,"publications":{}}');

        $this->expectException(InvalidArgumentException::class);
        $this->manifest->snapshot();
    }

    public function test_production_only_permits_reads_and_dry_runs(): void
    {
        $this->app->instance('env', 'production');
        $this->assertSame($this->data(), $this->manifest->import($this->data(), dryRun: true));
        $this->assertSame([], $this->manifest->snapshot()['publications']);

        $this->expectException(RuntimeException::class);
        $this->manifest->import($this->data());
    }
}
