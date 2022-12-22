<?php

namespace Neartech\{Module}\Providers;

use Illuminate\Support\ServiceProvider;

class {Module}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register();
    }

    public function boot()
    {
        /**
         * Load migrations file
         */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /**
         * Load route file
         */
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        /**
         * Load views folder
         */
        $this->loadViewsFrom(__DIR__.'/../../resources/views', '{module}');

        /**
         * Load lang folder
         */
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', '{module}');
    }
}
