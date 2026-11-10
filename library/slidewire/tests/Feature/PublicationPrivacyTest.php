<?php

namespace Tests\Feature;

use App\Logging\RedactSensitiveData;
use App\Models\User;
use App\Support\SpeakerDeckProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Http;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monolog;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PublicationPrivacyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_reject_anonymous_requests_even_from_loopback(): void
    {
        config(['publications.local_operator_ui' => true]);
        $this->get('/publications')->assertNotFound();
        $this->withSession(['_token' => 'fixture-csrf'])->post('/publications/associate', ['_token' => 'fixture-csrf'])->assertNotFound();
        $this->assertDatabaseCount('external_publications', 0);
    }

    public function test_production_routes_remain_disabled_even_for_an_authenticated_operator(): void
    {
        $user = User::factory()->create();
        config(['publications.local_operator_ui' => true, 'publications.operator_ids' => [(string) $user->id]]);
        $this->actingAs($user);
        $this->app->instance('env', 'production');
        $this->get('/publications')->assertNotFound();
        $this->withSession(['_token' => 'fixture-csrf'])->post('/publications/associate', ['_token' => 'fixture-csrf'])->assertNotFound();
    }

    public function test_provider_errors_never_include_response_bodies_or_chained_exceptions(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['credential' => 'fixture-private-detail'], 503)]);
        try {
            app(SpeakerDeckProvider::class)->getPublication('https://speakerdeck.com/matyo91/example', str_repeat('a', 32));
            $this->fail('Expected provider error.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Speaker Deck metadata request failed (HTTP 503).', $error->getMessage());
            $this->assertNull($error->getPrevious());
        }
    }

    public function test_public_host_is_denied_even_when_a_local_operator_is_authenticated(): void
    {
        $user = User::factory()->create();
        config(['publications.local_operator_ui' => true, 'publications.operator_ids' => [(string) $user->id]]);
        $this->actingAs($user)->get('https://slides.darkwood.com/publications')->assertNotFound();
    }

    public function test_standard_log_channels_all_have_the_redaction_tap(): void
    {
        foreach (config('logging.channels') as $channel) {
            if (isset($channel['driver'])) {
                $this->assertContains(RedactSensitiveData::class, $channel['tap'] ?? []);
            }
        }
    }

    public function test_git_boundaries_exclude_private_files_without_hiding_public_manifest(): void
    {
        foreach (['.env', '.env.local', '.env.production', 'database/database.sqlite', 'database/publication-registry.json', 'storage/app/private/publications/registry.json', 'docs/publication-report.json', 'backups/database.sql', 'credentials/private.pem'] as $path) {
            $process = new Process(['git', 'check-ignore', '--no-index', '-q', '--', $path], base_path());
            $process->run();
            $this->assertSame(0, $process->getExitCode(), $path);
        }
        foreach (['.env.example', 'resources/presentations.json', 'resources/data/publications.json'] as $path) {
            $process = new Process(['git', 'check-ignore', '--no-index', '-q', '--', $path], base_path());
            $process->run();
            $this->assertSame(1, $process->getExitCode(), $path);
        }
    }

    public function test_logging_redacts_nested_credentials_headers_urls_known_keys_and_exceptions(): void
    {
        config(['app.key' => 'fixture-application-private-value']);
        $handler = new TestHandler;
        $logger = new Logger(new Monolog('privacy', [$handler]));
        (new RedactSensitiveData)($logger);
        $logger->error('fixture-application-private-value password=fixture-password https://fixture-user:fixture-pass@example.test Cookie: session=fixture-cookie', [
            'nested' => ['api_token' => 'fixture-token', 'safe' => 42],
            'exception' => new \RuntimeException('fixture-exception-payload'),
        ]);
        $record = $handler->getRecords()[0];
        $encoded = json_encode([$record->message, $record->context]);
        foreach (['fixture-application-private-value', 'fixture-password', 'fixture-user', 'fixture-pass', 'fixture-cookie', 'fixture-token', 'fixture-exception-payload'] as $private) {
            $this->assertStringNotContainsString($private, $encoded);
        }
        $this->assertSame(42, $record->context['nested']['safe']);
    }
}
