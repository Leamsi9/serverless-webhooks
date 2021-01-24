<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * The path to the "home" route for your application.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        Route::macro('webhooksPost', function (string $url, string $name) {
            return Route::post($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}");
        });
        Route::macro('webhooksGet', function (string $url, string $name) {
            return Route::get($url, '\Spatie\WebhookClient\WebhookController')->name("webhook-client-{$name}");
        });

//      This replaces Spatie controller with a custom Xero one to handle validation requirements
        Route::macro('xeroGet', function (string $url, string $name) {
            return Route::get($url, '\App\Http\Controllers\XeroWebhookController')->name("webhook-client-{$name}");
        });

        Route::macro('xeroPost', function (string $url, string $name) {
            return Route::post($url, '\App\Http\Controllers\XeroWebhookController')->name("webhook-client-{$name}");
        });

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }
}
