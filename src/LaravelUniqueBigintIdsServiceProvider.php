<?php
declare(strict_types=1);

namespace AdamTheHutt\LaravelUniqueBigintIds;

use Illuminate\Support\ServiceProvider;

class LaravelUniqueBigintIdsServiceProvider extends ServiceProvider
{
    /**
     * Publishes configuration file.
     *
     * @return  void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/unique-bigint-ids.php',
            'unique-bigint-ids'
        );
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__.'/../config/unique-bigint-ids.php' => config_path('unique-bigint-ids.php'),
        ]);
    }
}
