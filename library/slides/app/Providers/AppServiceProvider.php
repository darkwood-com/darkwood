<?php

namespace App\Providers;

use App\Models\ExternalPublication;
use App\Models\PublicationAttempt;
use App\Support\PublicationRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use WendellAdriel\SlideWire\Commands\MakeSlideCommand;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Slidewire compiles the native Blade components directly.
        $this->app->bind(MakeSlideCommand::class, \App\Console\Commands\MakeSlideCommand::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->runningUnitTests()) {
            return;
        }
        foreach ([ExternalPublication::class, PublicationAttempt::class] as $model) {
            $model::saved(function () {
                DB::afterCommit(fn () => app(PublicationRegistry::class)->backup());
            });
        }
    }
}
