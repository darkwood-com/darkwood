<?php

namespace App\Providers;

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
        // Provider orchestration is intentionally implemented in private Slides Core.
    }
}
