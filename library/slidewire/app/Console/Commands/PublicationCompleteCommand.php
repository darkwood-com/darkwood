<?php

namespace App\Console\Commands;

use App\Models\PublicationAttempt;
use App\Support\AssistedPublication;
use Illuminate\Console\Command;

class PublicationCompleteCommand extends Command
{
    protected $signature = 'publications:complete {attempt} {remote_id} {url} {--confirmed-exact-artifact} {--confirmed-public-access}';

    protected $description = 'Record verified assisted upload results; hash proof is an operator attestation';

    public function handle(AssistedPublication $service): int
    {
        $publication = $service->complete(PublicationAttempt::findOrFail($this->argument('attempt')), $this->argument('remote_id'), $this->argument('url'), (bool) $this->option('confirmed-exact-artifact'), (bool) $this->option('confirmed-public-access'));
        $this->line($publication->toJson(JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
