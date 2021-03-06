<?php

namespace Nadneb\Smooth\Providers;

use Illuminate\Support\ServiceProvider;
use Nadneb\Smooth\Commands\SmoothDBCommand;

class SmoothProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SmoothDBCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__ . '/../../config/smooth.php' => config_path('smooth.php')
        ]);
    }
}