<?php

namespace App\Console\Commands;

use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;

class PublicationManifestCommand extends Command
{
    protected $signature = 'publications:manifest {--export} {--import= : Approved public manifest to import} {--accept-metadata-update : Accept changed PDF metadata, never changed remote IDs} {--dry-run} {--registry= : Canonical Slides Core registry path} {--sync-public : Copy the registry into the public authoring checkout}';

    protected $description = 'Inspect, explicitly export or safely import public identities; never uploads documents';

    public function handle(PublicPublicationManifest $manifest): int
    {
        if ($registry = $this->option('registry')) {
            $manifest = new PublicPublicationManifest($registry);
        }

        if ($this->option('export') && $this->option('import')) {
            $this->error('Choose export or import.');

            return self::FAILURE;
        }
        if ($this->option('sync-public')) {
            if ($this->option('export') || $this->option('import') || $this->option('dry-run') || $this->option('accept-metadata-update')) {
                $this->error('Synchronization cannot be combined with import, export or dry-run.');

                return self::FAILURE;
            }
            $manifest->syncPublic();
            $this->info('Public publication manifest synchronized.');

            return self::SUCCESS;
        }
        $file = $this->option('import');
        $data = $file
            ? $manifest->import($manifest->read($file), (bool) $this->option('accept-metadata-update'), (bool) $this->option('dry-run'))
            : $manifest->snapshot();
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
