<?php

namespace App\Console\Commands;

use App\Support\PublicPublicationManifest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        if ($file = $this->option('import')) {
            $data = json_decode(file_get_contents($file), true, flags: JSON_THROW_ON_ERROR);
            DB::beginTransaction();
            try {
                $manifest->import($data, (bool) $this->option('accept-metadata-update'));
                $this->option('dry-run') ? DB::rollBack() : DB::commit();
            } catch (\Throwable $error) {
                DB::rollBack();
                throw $error;
            }
        }
        $data = $manifest->snapshot();
        if ($this->option('export') && ! $this->option('dry-run')) {
            $manifest->export(resource_path('data/publications.json'));
        }
        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
