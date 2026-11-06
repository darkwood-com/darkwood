<?php

namespace App\Console\Commands;

use App\Models\Presentation;
use App\Support\AssistedPublication;
use Illuminate\Console\Command;

class PublicationPrepareCommand extends Command
{
    protected $signature = 'publications:prepare {id} {provider} {artifact} {--confirmed-replacement}';

    protected $description = 'Reserve an assisted publication attempt; never performs an upload';

    public function handle(AssistedPublication $service): int
    {
        $result = $service->prepare(Presentation::findOrFail($this->argument('id')), $this->argument('provider'), $this->argument('artifact'), (bool) $this->option('confirmed-replacement'));
        $this->line($result->toJson(JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
