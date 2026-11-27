<?php

namespace App\Support;

use Illuminate\Support\Str;
use RuntimeException;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class PresentationCatalog
{
    /** Read the canonical registry. */
    public function entries(?string $registry = null): array
    {
        $rows = json_decode(file_get_contents($registry ?? resource_path('presentations.json')), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($rows) || ! array_is_list($rows)) {
            throw new RuntimeException('Invalid canonical catalogue.');
        }

        $ids = $slugs = [];
        foreach ($rows as $row) {
            if (! is_array($row) || ! is_string($row['id'] ?? null) || ! is_string($row['source_slug'] ?? null)
                || ! is_string($row['title'] ?? null) || ! Str::isUuid($row['id']) || isset($ids[$row['id']]) || isset($slugs[$row['source_slug']]) || ! preg_match('/^[a-z0-9-]+$/D', $row['source_slug'])) {
                throw new RuntimeException('Invalid or duplicate canonical identity.');
            }
            $ids[$row['id']] = $slugs[$row['source_slug']] = true;
        }

        return $rows;
    }

    public function find(string $id): array
    {
        return collect($this->entries())->firstWhere('id', $id)
            ?? throw new RuntimeException('Unknown canonical presentation UUID.');
    }

    /** IDs are allocated once in the versioned registry; slugs are mutable locators. */
    public function compiledEntries(?string $registry = null): array
    {
        $rows = $this->entries($registry);
        $compiler = app(PresentationCompiler::class);
        foreach ($rows as &$row) {
            $slides = $compiler->flattenSlides($compiler->compile($row['source_slug'])['slides']);
            if ($slides === []) {
                throw new RuntimeException('Missing source: '.$row['source_slug']);
            }
            $row['slide_count'] = count($slides);
        }
        unset($row);
        return $rows;
    }
}
