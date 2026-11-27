<?php

namespace Tests\Feature;

use App\Logging\RedactSensitiveData;
use Illuminate\Log\Logger;
use Monolog\Handler\TestHandler;
use Monolog\Logger as Monolog;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PublicationPrivacyTest extends TestCase
{
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
        foreach (['.env', '.env.local', '.env.production', 'database/database.sqlite', 'storage/app/private/publications/registry.json', 'docs/publication-report.json', 'backups/database.sql', 'credentials/private.pem'] as $path) {
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
