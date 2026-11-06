<?php

namespace App\Support;

use App\Models\ExternalPublication;
use App\Models\Presentation;
use App\Models\PublicationAttempt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AssistedPublication
{
    public function __construct(private PublicationProviders $providers) {}

    public function prepare(Presentation $deck, string $provider, string $artifact, bool $replacementConfirmed = false): PublicationAttempt|ExternalPublication
    {
        $capabilities = $this->providers->capabilities($provider);
        $file = realpath($artifact);
        $root = realpath(storage_path('app/publications/'.$deck->id));
        if (! $file || ! $root || ! str_starts_with($file, $root.DIRECTORY_SEPARATOR) || basename($file) !== 'presentation.pdf') {
            throw new InvalidArgumentException('Use a validated canonical export for this presentation.');
        }
        $validation = json_decode(file_get_contents(dirname($file).'/pdf-validation.json'), true, flags: JSON_THROW_ON_ERROR);
        $manifest = json_decode(file_get_contents(dirname($file).'/manifest.json'), true, flags: JSON_THROW_ON_ERROR);
        if (($manifest['id'] ?? '') !== $deck->id || ($manifest['revision'] ?? '') !== app(CanonicalRevision::class)->hash()) {
            throw new InvalidArgumentException('Stale source revision; generate a fresh canonical export.');
        }
        $hash = hash_file('sha256', $file);
        if (($validation['sha256'] ?? '') !== $hash || ($validation['pages'] ?? 0) !== $deck->slide_count) {
            throw new InvalidArgumentException('Artifact validation mismatch.');
        }

        return DB::transaction(function () use ($deck, $provider, $file, $hash, $replacementConfirmed, $capabilities) {
            $publication = ExternalPublication::firstOrCreate(['slide_id' => $deck->id, 'provider' => $provider]);
            $publication = ExternalPublication::whereKey($publication->id)->lockForUpdate()->firstOrFail();
            if (PublicationAttempt::where('external_publication_id', $publication->id)->where('active_slot', 1)->exists()) {
                throw new InvalidArgumentException('An unresolved attempt exists. Reconcile it before any new upload.');
            }
            if ($publication->remote_id && $publication->source_hash === $hash) {
                return $publication;
            }
            $replace = (bool) $publication->remote_id;
            if ($replace && (! $replacementConfirmed || $capabilities['replaceDocument'] !== 'assisted_confirmation_required')) {
                $publication->update(['status' => 'update_available']);

                return $publication;
            }
            if (! $replace && $capabilities['create'] !== 'assisted') {
                $publication->update(['status' => 'manual_action_required', 'last_error' => 'Provider uploads unavailable; no attempt created.']);

                return $publication;
            }
            $publication->update(['status' => 'manual_action_required', 'last_error' => null]);

            return PublicationAttempt::create(['external_publication_id' => $publication->id, 'source_hash' => $hash, 'artifact_path' => $file, 'operation' => $replace ? 'replace' : 'create', 'active_slot' => 1]);
        });
    }

    /** Must follow browser verification and explicit attestation of the exact PDF upload. */
    public function complete(PublicationAttempt $attempt, string $remoteId, string $url, bool $exactArtifactConfirmed, bool $publiclyAccessibleConfirmed): ExternalPublication
    {
        if (! $exactArtifactConfirmed || ! $publiclyAccessibleConfirmed) {
            throw new InvalidArgumentException('Confirm the exact uploaded artifact and public accessibility.');
        }

        return DB::transaction(function () use ($attempt, $remoteId, $url) {
            $attempt = PublicationAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            if ($attempt->active_slot !== 1) {
                throw new InvalidArgumentException('Attempt already closed.');
            }
            if (! is_file($attempt->artifact_path) || hash_file('sha256', $attempt->artifact_path) !== $attempt->source_hash) {
                throw new InvalidArgumentException('Artifact changed since preparation.');
            }
            $publication = ExternalPublication::whereKey($attempt->external_publication_id)->lockForUpdate()->firstOrFail();
            $this->providers->validateIdentity($publication->provider, $remoteId, $url);
            if ($publication->remote_id && $publication->remote_id !== $remoteId) {
                throw new InvalidArgumentException('Remote identity changed; refusing replacement or duplicate.');
            }
            $publication->update(['remote_id' => $remoteId, 'remote_url' => $url, 'source_hash' => $attempt->source_hash, 'status' => 'published', 'published_at' => $publication->published_at ?? now(), 'last_synced_at' => now(), 'last_error' => null]);
            $attempt->update(['status' => 'completed_manual_attestation', 'active_slot' => null]);

            return $publication;
        });
    }
}
