<?php

namespace App\Support;

use App\Models\Presentation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class PresentationCatalog
{
    /** Read the single canonical registry without requiring database writes. */
    public function entries(?string $registry = null): array
    {
        $rows = json_decode(file_get_contents($registry ?? resource_path('presentations.json')), true, flags: JSON_THROW_ON_ERROR);
        $ids = $slugs = [];
        foreach ($rows as $row) {
            if (! Str::isUuid($row['id']) || isset($ids[$row['id']]) || isset($slugs[$row['source_slug']]) || ! preg_match('/^[a-z0-9-]+$/D', $row['source_slug'])) {
                throw new RuntimeException('Invalid or duplicate canonical identity.');
            }
            $ids[$row['id']] = $slugs[$row['source_slug']] = true;
        }

        return $rows;
    }

    /** IDs are allocated once in the versioned registry; slugs are mutable locators. */
    public function synchronize(?string $registry = null): array
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
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                Presentation::updateOrCreate(['id' => $row['id']], $row);
            }
        });

        return $rows;
    }
}
