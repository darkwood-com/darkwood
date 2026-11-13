<?php

namespace App\Support;

use App\Models\ExternalPublication;
use App\Models\Presentation;
use App\Models\PublicationAttempt;
use Illuminate\Support\Facades\File;

/** Private recovery journal. Never write this snapshot into the Git working tree. */
class PublicationRegistry
{
    public function snapshot(): array
    {
        return [
            'schema_version' => 1,
            'presentations' => Presentation::orderBy('id')->get()->toArray(),
            'publications' => ExternalPublication::orderBy('slide_id')->orderBy('provider')->get()->toArray(),
            'attempts' => PublicationAttempt::orderBy('id')->get()->toArray(),
        ];
    }

    public function backup(): void
    {
        $path = storage_path('app/private/publications/registry.json');
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0700, true);
        }
        chmod(dirname($path), 0700);
        $temporary = tempnam(dirname($path), '.publication-registry-');
        try {
            File::put($temporary, json_encode($this->snapshot(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");
            chmod($temporary, 0600);
            if (! rename($temporary, $path)) {
                throw new \RuntimeException('Publication registry backup failed.');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
