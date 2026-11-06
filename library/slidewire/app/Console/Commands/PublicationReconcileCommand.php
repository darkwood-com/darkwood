<?php

namespace App\Console\Commands;

use App\Models\PublicationAttempt;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PublicationReconcileCommand extends Command
{
    protected $signature = 'publications:reconcile {attempt} {--error=interrupted : interrupted, authentication_failed or upload_failed} {--confirmed-no-remote-publication : Close only after checking the provider account}';

    protected $description = 'Record an interrupted/failed assisted upload and reconcile without automatically retrying';

    public function handle(): int
    {
        if (! in_array($this->option('error'), ['interrupted', 'authentication_failed', 'upload_failed'], true)) {
            $this->error('Unsupported error code.');

            return self::FAILURE;
        }
        DB::transaction(function () {
            $attempt = PublicationAttempt::whereKey($this->argument('attempt'))->lockForUpdate()->firstOrFail();
            if ($attempt->active_slot !== 1) {
                throw new \InvalidArgumentException('Attempt already closed.');
            }
            $confirmed = (bool) $this->option('confirmed-no-remote-publication');
            if ($confirmed && $attempt->operation !== 'create') {
                throw new \InvalidArgumentException('Replacement attempts must be reconciled against the existing remote publication.');
            }
            $attempt->update(['last_error' => $this->option('error'), 'status' => $confirmed ? 'failed_confirmed_no_publication' : 'manual_reconciliation_required', 'active_slot' => $confirmed ? null : 1]);
        });
        $this->info('Attempt recorded. No remote action performed.');

        return self::SUCCESS;
    }
}
