<?php

namespace App\Console\Commands;

use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;

class PublicationManifestCommand extends Command
{
    protected $signature = 'publications:manifest {--export} {--import= : Approved public manifest to import} {--accept-metadata-update : Accept changed PDF metadata, never changed remote IDs} {--dry-run}';

    protected $description = 'Inspect, explicitly export or safely import public identities; never uploads documents';

    public function handle(PublicPublicationManifest $manifest): int
    {
        if ($this->option('export') && $this->option('import')) {
            $this->error('Choose export or import.');

            return self::FAILURE;
        }
        $file = $this->option('import');
        $data = $file
            ? $manifest->import($manifest->read($file), (bool) $this->option('accept-metadata-update'), (bool) $this->option('dry-run'))
            : $manifest->snapshot();
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
