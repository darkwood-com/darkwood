<?php

namespace App\Providers;

use App\Support\DarkwoodPresentationCompiler;
use Illuminate\Support\ServiceProvider;
use WendellAdriel\SlideWire\Support\PresentationCompiler;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PresentationCompiler::class, DarkwoodPresentationCompiler::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
