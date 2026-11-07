<?php

namespace Tests\Feature;

use App\Models\ExternalPublication;
use App\Models\PublicationAttempt;
use App\Support\PresentationCatalog;
use App\Support\PublicationRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicationRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_preserves_remote_identity_hash_and_unresolved_attempts(): void
    {
        app(PresentationCatalog::class)->synchronize();
        $deck = DB::table('presentations')->first();
        $publication = ExternalPublication::create(['slide_id' => $deck->id, 'provider' => 'speakerdeck',
            'remote_id' => str_repeat('a', 32), 'remote_url' => 'https://speakerdeck.com/matyo91/example',
            'source_hash' => str_repeat('b', 64), 'status' => 'published']);
        PublicationAttempt::create(['external_publication_id' => $publication->id, 'source_hash' => str_repeat('c', 64), 'artifact_path' => '/tmp/test.pdf', 'operation' => 'replace', 'active_slot' => 1]);
        $snapshot = app(PublicationRegistry::class)->snapshot();
        $this->assertCount(28, $snapshot['presentations']);
        $this->assertSame($publication->remote_id, $snapshot['publications'][0]['remote_id']);
        $this->assertSame($publication->source_hash, $snapshot['publications'][0]['source_hash']);
        $this->assertSame(1, $snapshot['attempts'][0]['active_slot']);
        $this->artisan('publications:report', ['--missing' => true])->assertSuccessful();
    }

    public function test_html_report_contains_real_identity_and_escapes_errors(): void
    {
        app(PresentationCatalog::class)->synchronize();
        $deck = DB::table('presentations')->first();
        ExternalPublication::create(['slide_id' => $deck->id, 'provider' => 'speakerdeck', 'last_error' => '<script>alert(1)</script>']);
        $file = tempnam(sys_get_temp_dir(), 'slidewire-report-');
        try {
            $this->artisan('publications:report', ['--html' => true, '--output' => $file])->assertSuccessful();
            $html = file_get_contents($file);
            $this->assertStringContainsString($deck->id, $html);
            $this->assertStringContainsString('publication_action_required', $html);
            $this->assertStringNotContainsString('&lt;script&gt;', $html);
            $this->assertStringNotContainsString('<script>', $html);
        } finally {
            unlink($file);
        }
    }

    public function test_restore_dry_run_exercises_constraints_and_rolls_back(): void
    {
        app(PresentationCatalog::class)->synchronize();
        $snapshot = app(PublicationRegistry::class)->snapshot();
        DB::table('presentations')->delete();
        $file = tempnam(sys_get_temp_dir(), 'slidewire-restore-');
        try {
            file_put_contents($file, json_encode($snapshot));
            $this->artisan('publications:registry', ['--restore' => $file, '--dry-run' => true])->assertSuccessful();
            $this->assertDatabaseCount('presentations', 0);
        } finally {
            unlink($file);
        }
    }
}
