<?php

declare(strict_types=1);

namespace App\Infrastructure\Preference;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class JsonPreferenceRepository
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/data/preferences')]
        private readonly string $directory,
    ) {
    }

    /**
     * @param array<string,mixed> $record
     */
    public function save(array $record): string
    {
        $id = $record['id'] ?? null;
        if (!\is_string($id) || $id === '') {
            throw new \InvalidArgumentException('Preference record must define a string id.');
        }

        if (!is_dir($this->directory) && !mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new \RuntimeException(sprintf('Unable to create preference directory "%s".', $this->directory));
        }

        $path = sprintf('%s/%s.json', $this->directory, $this->safeId($id));
        $encoded = json_encode($record, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . "\n";
        if (file_put_contents($path, $encoded) === false) {
            throw new \RuntimeException(sprintf('Unable to write preference record "%s".', $path));
        }

        return $path;
    }

    /**
     * @return array<string,mixed>
     */
    public function load(string $id): array
    {
        $path = sprintf('%s/%s.json', $this->directory, $this->safeId($id));
        if (!is_file($path)) {
            throw new \InvalidArgumentException(sprintf('Preference record "%s" does not exist.', $id));
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!\is_array($decoded)) {
            throw new \RuntimeException('Preference record JSON must decode to an object.');
        }

        return $decoded;
    }

    private function safeId(string $id): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $id) ?? '';
        if ($safe === '') {
            throw new \InvalidArgumentException('Preference record id is invalid.');
        }

        return $safe;
    }
}
