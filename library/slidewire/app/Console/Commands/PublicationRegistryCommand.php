<?php

namespace App\Console\Commands;

use App\Models\ExternalPublication;
use App\Models\Presentation;
use App\Models\PublicationAttempt;
use App\Support\PublicationProviders;
use App\Support\PublicationRegistry;
use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublicationRegistryCommand extends Command
{
    protected $signature = 'publications:registry {--restore= : Restore a backup into empty tracking tables} {--dry-run}';

    protected $description = 'Export the durable publication journal, or restore it without overwriting existing identities';

    public function handle(PublicationRegistry $registry, PublicationProviders $providers): int
    {
        if ($file = $this->option('restore')) {
            $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            if (($data['schema_version'] ?? null) !== 1) {
                throw new \InvalidArgumentException('Unknown registry format.');
            }
            foreach ($data['publications'] as $row) {
                if ($row['remote_id']) {
                    $providers->validateIdentity($row['provider'], $row['remote_id'], $row['remote_url']);
                }
            }
            DB::beginTransaction();
            try {
                if (Presentation::exists() || ExternalPublication::exists() || PublicationAttempt::exists()) {
                    throw new \InvalidArgumentException('Restore requires empty tracking tables; existing records are never overwritten.');
                }
                foreach (['presentations', 'external_publications', 'publication_attempts'] as $i => $table) {
                    $rows = $data[['presentations', 'publications', 'attempts'][$i]];
                    foreach ($rows as $row) {
                        DB::table($table)->insert($row);
                    }
                }
                if ($this->option('dry-run')) {
                    DB::rollBack();
                } else {
                    DB::commit();
                }
            } catch (\Throwable $error) {
                DB::rollBack();
                throw $error;
            }
        }
        if (! $this->option('dry-run')) {
            $registry->backup();
        }
        // Full recovery material stays in the private backup, never in normal diagnostics.
        $this->line(json_encode(app(PublicPublicationManifest::class)->snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
