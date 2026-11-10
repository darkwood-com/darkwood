<?php

declare(strict_types=1);

namespace App\Token;

use RuntimeException;

/**
 * File-backed reference store: var/context/<id>.json
 */
final class ContextStore
{
    public function __construct(
        private readonly string $directory = '',
    ) {}

    public function directory(): string
    {
        if ($this->directory !== '') {
            return $this->directory;
        }

        return dirname(__DIR__, 2) . '/var/context';
    }

    public function put(ContextReference $reference): void
    {
        $dir = $this->directory();
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Cannot create context store: %s', $dir));
        }

        $path = $this->pathFor($reference->id);
        if (is_file($path)) {
            $existing = $this->get($reference->id);
            if ($existing !== null && $existing->content !== $reference->content) {
                throw new RuntimeException(sprintf('Reference id collision for %s', $reference->id));
            }
        }

        $json = json_encode($reference->toArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new RuntimeException('Failed to encode context reference');
        }

        if (file_put_contents($path, $json . "\n") === false) {
            throw new RuntimeException(sprintf('Failed to write reference: %s', $path));
        }
    }

    public function get(string $id): ?ContextReference
    {
        $id = $this->normalizeId($id);
        $path = $this->pathFor($id);
        if (!is_readable($path)) {
            return null;
        }

        $json = file_get_contents($path);
        if ($json === false) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            return null;
        }

        /** @var array{id?: mixed, source?: mixed, type?: mixed, reason?: mixed, content?: mixed} $data */
        return ContextReference::fromArray($data);
    }

    public function pathFor(string $id): string
    {
        return $this->directory() . '/' . $this->normalizeId($id) . '.json';
    }

    /**
     * Deterministic short id: ctx_ + first 6 hex of sha256(source|reason|content).
     * Extends length on content collision with an existing different blob.
     */
    public function makeId(string $source, string $reason, string $content): string
    {
        $hash = hash('sha256', $source . '|' . $reason . '|' . $content);
        foreach ([6, 8, 10, 12, 16] as $len) {
            $id = 'ctx_' . substr($hash, 0, $len);
            $existing = $this->get($id);
            if ($existing === null || $existing->content === $content) {
                return $id;
            }
        }

        throw new RuntimeException('Unable to allocate unique reference id');
    }

    private function normalizeId(string $id): string
    {
        return str_starts_with($id, '#ref:') ? substr($id, 5) : $id;
    }
}
