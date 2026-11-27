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
        // Laravel merges its built-in SQL connections into the project configuration.
        $this->app['config']->set('database.connections', []);

        // Slidewire compiles the native Blade components directly.
        $this->app->bind(MakeSlideCommand::class, \App\Console\Commands\MakeSlideCommand::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Provider orchestration is intentionally implemented in private Slide Core.
    }
}
