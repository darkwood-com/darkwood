<?php

namespace App\Support;

use App\Models\ExternalPublication;
use App\Models\PublicationAttempt;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/** Explicit public projection of the operational database, not a second job journal. */
class PublicPublicationManifest
{
    public const FIELDS = ['slide_id', 'provider', 'remote_id', 'remote_url', 'source_hash', 'published_at', 'last_synced_at'];

    public function snapshot(): array
    {
        $data = ['schema_version' => 1, 'publications' => ExternalPublication::whereNotNull('remote_id')
            ->orderBy('slide_id')->orderBy('provider')->get()->map(fn ($p) => array_replace(array_fill_keys(self::FIELDS, null), array_intersect_key($p->toArray(), array_flip(self::FIELDS))))->all()];
        $this->validate($data);

        return $data;
    }

    public function validate(array $data): void
    {
        if (($data['schema_version'] ?? null) !== 1 || array_diff(array_keys($data), ['schema_version', 'publications']) || ! is_array($data['publications'] ?? null) || ! array_is_list($data['publications'])) {
            throw new InvalidArgumentException('Invalid public manifest schema.');
        }
        $known = array_column(app(PresentationCatalog::class)->entries(), 'id');
        $pairs = $remotes = [];
        foreach ($data['publications'] as $row) {
            if (! is_array($row) || array_diff(array_keys($row), self::FIELDS) || ! in_array($row['slide_id'] ?? null, $known, true)) {
                throw new InvalidArgumentException('Unknown presentation or non-public manifest fields.');
            }
            foreach (['provider', 'remote_id', 'remote_url'] as $field) {
                if (! is_string($row[$field] ?? null)) {
                    throw new InvalidArgumentException('Incomplete public identity.');
                }
            }
            app(PublicationProviders::class)->validateIdentity($row['provider'], $row['remote_id'], $row['remote_url']);
            if (isset($row['source_hash']) && (! is_string($row['source_hash']) || ! preg_match('/^[a-f0-9]{64}$/D', $row['source_hash']))) {
                throw new InvalidArgumentException('Invalid public PDF hash.');
            }
            foreach (['published_at', 'last_synced_at'] as $field) {
                if (isset($row[$field]) && (! is_string($row[$field]) || ! preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2})$/D', $row[$field]))) {
                    throw new InvalidArgumentException('Invalid public publication date.');
                }
                if (isset($row[$field])) {
                    try {
                        new \DateTimeImmutable($row[$field]);
                        $errors = \DateTimeImmutable::getLastErrors();
                        if ($errors && ($errors['warning_count'] || $errors['error_count'])) {
                            throw new \RuntimeException;
                        }
                    } catch (\Throwable) {
                        throw new InvalidArgumentException('Invalid public publication date.');
                    }
                }
            }
            $pair = $row['slide_id'].':'.$row['provider'];
            $remote = $row['provider'].':'.$row['remote_id'];
            if (isset($pairs[$pair]) || isset($remotes[$remote])) {
                throw new InvalidArgumentException('Duplicate public mapping or remote identity.');
            }
            $pairs[$pair] = $remotes[$remote] = true;
        }
    }

    public function export(string $path): void
    {
        $data = $this->snapshot();
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        $temporary = tempnam(dirname($path), '.publications-');
        try {
            file_put_contents($temporary, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
            chmod($temporary, 0644);
            if (! rename($temporary, $path)) {
                throw new \RuntimeException('Public manifest export failed.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    public function import(array $data, bool $acceptMetadataUpdate = false): void
    {
        $this->validate($data);
        DB::transaction(function () use ($data, $acceptMetadataUpdate) {
            app(PresentationCatalog::class)->synchronize();
            foreach ($data['publications'] as $row) {
                $p = ExternalPublication::firstOrNew(['slide_id' => $row['slide_id'], 'provider' => $row['provider']]);
                if ($p->exists) {
                    $p = ExternalPublication::whereKey($p->id)->lockForUpdate()->firstOrFail();
                }
                if ($p->remote_id && $p->remote_id !== $row['remote_id']) {
                    throw new InvalidArgumentException('Remote identity conflict; no association overwritten.');
                }
                if ($p->source_hash && empty($row['source_hash'])) {
                    throw new InvalidArgumentException('A known published hash cannot be erased by an import.');
                }
                if ($p->remote_id && $p->source_hash && $p->source_hash !== ($row['source_hash'] ?? null) && ! $acceptMetadataUpdate) {
                    throw new InvalidArgumentException('PDF hash conflict; review before accepting metadata updates.');
                }
                if ($p->remote_id && $p->remote_url !== $row['remote_url'] && ! $acceptMetadataUpdate) {
                    throw new InvalidArgumentException('Public URL conflict; review before accepting metadata updates.');
                }
                foreach (['published_at', 'last_synced_at'] as $field) {
                    if ($p->$field && empty($row[$field])) {
                        unset($row[$field]); // Unknown imported dates never erase known attestations.
                    }
                }
                if ($p->exists && PublicationAttempt::where('external_publication_id', $p->id)->where('active_slot', 1)->exists()) {
                    throw new InvalidArgumentException('Unresolved local attempt; reconcile before importing.');
                }
                $owner = ExternalPublication::where('provider', $row['provider'])->where('remote_id', $row['remote_id'])->first();
                if ($owner && $owner->slide_id !== $row['slide_id']) {
                    throw new InvalidArgumentException('Remote identity already belongs to another presentation.');
                }
                $p->fill(array_intersect_key($row, array_flip(self::FIELDS)));
                if (! $p->exists || ! $p->getOriginal('remote_id')) {
                    $p->status = 'imported_public_manifest';
                }
                if ($p->isDirty()) {
                    $p->save();
                }
            }
        });
    }
}
