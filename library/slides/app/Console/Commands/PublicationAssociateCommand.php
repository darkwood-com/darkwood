<?php

namespace App\Console\Commands;

use App\Models\Presentation;
use App\Support\PublicationAssociations;
use Illuminate\Console\Command;

class PublicationAssociateCommand extends Command
{
    protected $signature = 'publications:associate {id} {provider} {remote_id} {url} {--confirmed-correspondence : Explicitly confirm editorial correspondence}';

    protected $description = 'Associate an existing remote deck without uploading or asserting PDF equality';

    public function handle(PublicationAssociations $associations): int
    {
        $publication = $associations->associate(Presentation::findOrFail($this->argument('id')), $this->argument('provider'), $this->argument('remote_id'), $this->argument('url'), (bool) $this->option('confirmed-correspondence'));
        $this->line($publication->toJson(JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
