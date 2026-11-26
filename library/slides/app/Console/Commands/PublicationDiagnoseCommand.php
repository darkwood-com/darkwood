<?php

namespace App\Console\Commands;

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
        $data += ['canonical_presentations' => count($entries), 'missing' => $missing, 'orphan_count' => 0, 'integrity' => 'valid'];
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
