<?php

namespace App\Console\Commands;

use App\Models\Presentation;
use App\Support\AssistedPublication;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublicationPrepareCommand extends Command
{
    protected $signature = 'publications:prepare {id} {provider} {artifact} {--confirmed-replacement} {--dry-run}';

    protected $description = 'Reserve an assisted publication attempt; never performs an upload';

    public function handle(AssistedPublication $service): int
    {
        if ($this->option('dry-run')) {
            DB::beginTransaction();
        }
        try {
            $result = $service->prepare(Presentation::findOrFail($this->argument('id')), $this->argument('provider'), $this->argument('artifact'), (bool) $this->option('confirmed-replacement'));
            $this->line($result->toJson(JSON_PRETTY_PRINT));
        } finally {
            if ($this->option('dry-run')) {
                DB::rollBack();
            }
        }

        return self::SUCCESS;
    }
}
