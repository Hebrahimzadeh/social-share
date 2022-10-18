<?php

namespace Hebrahimzadeh\Share;

use Illuminate\Support\ServiceProvider;

class ShareServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/social-share.php';
        $this->publishes([$configPath => config_path('social-share.php')]);
        $this->loadViewsFrom(__DIR__ . '/../views', 'social-share');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang/', 'laravel-share');
        $this->publishes([
            __DIR__ . '/../../resources/lang/' => resource_path('lang/vendor/laravel-share'),
        ], 'translations');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $configPath = __DIR__ . '/../config/social-share.php';
        $this->mergeConfigFrom($configPath, 'social-share');

        $this->app->singleton('share', fn ($app) => new Share($app));
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['share'];
    }
}
