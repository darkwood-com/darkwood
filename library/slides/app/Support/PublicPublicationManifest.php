<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class PublicPublicationManifest
{
    public const FIELDS = ['slide_id', 'provider', 'remote_id', 'remote_url', 'source_hash', 'published_at', 'last_synced_at'];

    public function __construct(private ?string $path = null) {}

    public function snapshot(): array
    {
        return $this->read($this->path ?? resource_path('data/publications.json'));
    }

    public function read(string $path): array
    {
        $decoded = json_decode(File::get($path), flags: JSON_THROW_ON_ERROR);
        if (! $decoded instanceof \stdClass || ! is_array($decoded->publications ?? null)) {
            throw new InvalidArgumentException('Invalid public manifest schema.');
        }
        $data = (array) $decoded;
        $data['publications'] = array_map(static fn ($row) => $row instanceof \stdClass ? (array) $row : $row, $decoded->publications);
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
            app(RemotePublicationIdentity::class)->validate($row['provider'], $row['remote_id'], $row['remote_url']);
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

    public function associate(string $id, string $provider, string $remoteId, string $url, bool $confirmed): array
    {
        if (! $confirmed) {
            throw new InvalidArgumentException('The editorial correspondence must be explicitly confirmed.');
        }

        $row = ['slide_id' => $id, 'provider' => $provider, 'remote_id' => $remoteId, 'remote_url' => $url];
        $next = $this->merge(['schema_version' => 1, 'publications' => [$row]], true);

        return collect($next['publications'])->first(fn (array $publication): bool => $publication['slide_id'] === $id && $publication['provider'] === $provider);
    }

    public function import(array $data, bool $acceptMetadataUpdate = false, bool $dryRun = false): array
    {
        return $this->merge($data, $acceptMetadataUpdate, $dryRun);
    }

    private function merge(array $data, bool $acceptMetadataUpdate, bool $dryRun = false): array
    {
        $this->validate($data);
        if ($dryRun) {
            return $this->merged($this->snapshot(), $data, $acceptMetadataUpdate);
        }
        $path = $this->writableRegistry();

        return $this->withLock($path, function () use ($path, $data, $acceptMetadataUpdate): array {
            $current = $this->snapshot();
            $next = $this->merged($current, $data, $acceptMetadataUpdate);
            if ($next !== $current) {
                $this->write($path, $next);
            }

            return $next;
        });
    }

    public function syncPublic(): void
    {
        $registry = $this->writableRegistry();
        $public = resource_path('data/publications.json');
        $this->withLock($registry, function () use ($public): void {
            $registry = $this->snapshot();
            $this->withLock($public, function () use ($public, $registry): void {
                $previous = $this->read($public);
                $merged = $this->merged($previous, $registry, true);
                if ($merged !== $registry) {
                    throw new RuntimeException('The public projection contains associations or metadata missing from the canonical registry. Reconcile them first.');
                }
                if ($previous !== $registry) {
                    $this->write($public, $registry);
                }
            });
        });
    }

    private function writableRegistry(): string
    {
        if (app()->isProduction() || $this->path === null
            || realpath($this->path) === realpath(resource_path('data/publications.json'))) {
            throw new RuntimeException('Write associations in the Slides Core authoring registry and deploy the generated public manifest with Git.');
        }

        return $this->path;
    }

    private function withLock(string $path, callable $operation): mixed
    {
        $lock = fopen(dirname($path).'/.'.basename($path).'.lock', 'c');
        if ($lock === false) {
            throw new RuntimeException('Unable to open the public manifest lock.');
        }
        try {
            if (! flock($lock, LOCK_EX)) {
                throw new RuntimeException('Unable to lock the public manifest.');
            }

            return $operation();
        } finally {
            fclose($lock);
        }
    }

    private function merged(array $current, array $incoming, bool $acceptMetadataUpdate): array
    {
        $publications = [];
        foreach ($current['publications'] as $row) {
            $publications[$row['slide_id'].':'.$row['provider']] = $row;
        }

        foreach ($incoming['publications'] as $row) {
            $key = $row['slide_id'].':'.$row['provider'];
            $previous = $publications[$key] ?? null;
            if ($previous !== null) {
                if ($previous['remote_id'] !== $row['remote_id']) {
                    throw new InvalidArgumentException('Remote identity conflict; no association overwritten.');
                }
                if (isset($previous['source_hash']) && array_key_exists('source_hash', $row) && $row['source_hash'] === null) {
                    throw new InvalidArgumentException('A known published hash cannot be erased by an import.');
                }
                if (! $acceptMetadataUpdate
                    && ($previous['remote_url'] !== $row['remote_url']
                        || (isset($previous['source_hash'], $row['source_hash']) && $previous['source_hash'] !== $row['source_hash']))) {
                    throw new InvalidArgumentException('PDF hash or public URL conflict; review before accepting metadata updates.');
                }
                foreach (['published_at', 'last_synced_at'] as $field) {
                    if (isset($previous[$field]) && ! isset($row[$field])) {
                        unset($row[$field]);
                    }
                }
            }

            $publications[$key] = array_replace(array_fill_keys(self::FIELDS, null), $previous ?? [], $row);
        }

        ksort($publications);
        $next = ['schema_version' => 1, 'publications' => array_values($publications)];
        $this->validate($next);

        return $next;
    }

    private function write(string $path, array $data): void
    {
        $temporary = tempnam(dirname($path), '.publications-');
        if ($temporary === false) {
            throw new RuntimeException('Unable to stage the public manifest.');
        }
        try {
            $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
            if (file_put_contents($temporary, $json) !== strlen($json) || ! chmod($temporary, 0644) || ! rename($temporary, $path)) {
                throw new RuntimeException('Public manifest write failed.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
