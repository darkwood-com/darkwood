<?php

namespace App\Console\Commands;

use App\Support\PresentationCatalog;
use Illuminate\Console\Command;

class PublicationCatalogCommand extends Command
{
    protected $signature = 'publications:catalog';

    protected $description = 'Synchronize canonical UUIDs and inventory compiled presentations';

    public function handle(PresentationCatalog $catalog): int
    {
        $rows = $catalog->synchronize();
        $this->line(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
