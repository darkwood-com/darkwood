<?php

namespace App\Console\Commands;

use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;

class PublicationAssociateCommand extends Command
{
    protected $signature = 'publications:associate {id} {provider} {remote_id} {url} {--confirmed-correspondence : Explicitly confirm editorial correspondence} {--registry= : Canonical Slides Core registry path}';

    protected $description = 'Associate an existing remote deck without uploading or asserting PDF equality';

    public function handle(PublicPublicationManifest $manifest): int
    {
        if ($registry = $this->option('registry')) {
            $manifest = new PublicPublicationManifest($registry);
        }

        $publication = $manifest->associate($this->argument('id'), $this->argument('provider'), $this->argument('remote_id'), $this->argument('url'), (bool) $this->option('confirmed-correspondence'));
        $this->line(json_encode($publication, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}
