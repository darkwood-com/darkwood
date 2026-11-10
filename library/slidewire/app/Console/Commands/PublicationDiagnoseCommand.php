<?php

namespace App\Console\Commands;

use App\Models\ExternalPublication;
use App\Models\PublicationAttempt;
use App\Support\PresentationCatalog;
use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;

class PublicationDiagnoseCommand extends Command
{
    protected $signature = 'publications:diagnose {--json}';

    protected $description = 'Read-only public mapping, missing associations and integrity counts; no errors, paths or credentials';

    public function handle(PresentationCatalog $catalog, PublicPublicationManifest $manifest): int
    {
        $entries = $catalog->entries();
        $orphans = ExternalPublication::whereNotIn('slide_id', array_column($entries, 'id'))->count();
        if ($orphans) {
            $this->line(json_encode(['canonical_presentations' => count($entries), 'orphan_count' => $orphans, 'integrity' => 'orphaned_mapping'], JSON_PRETTY_PRINT));

            return self::FAILURE;
        }
        $data = $manifest->snapshot();
        $pairs = collect($data['publications'])->map(fn ($p) => $p['slide_id'].':'.$p['provider'])->all();
        $missing = [];
        foreach ($entries as $entry) {
            foreach (['speakerdeck', 'slideshare'] as $provider) {
                if (! in_array($entry['id'].':'.$provider, $pairs, true)) {
                    $missing[] = ['slide_id' => $entry['id'], 'provider' => $provider];
                }
            }
        }
        $file = resource_path('data/publications.json');
        $export = is_file($file) ? json_decode(file_get_contents($file), true) : null;
        $data += ['canonical_presentations' => count($entries), 'missing' => $missing,
            'orphan_count' => $orphans, 'active_attempt_count' => PublicationAttempt::where('active_slot', 1)->count(),
            'public_manifest_exists' => is_file($file), 'public_manifest_matches_database' => $data === $export];
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $orphans ? self::FAILURE : self::SUCCESS;
    }
}
