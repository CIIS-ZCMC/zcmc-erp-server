<?php

namespace App\Providers;

use App\Auth\AuthCookieGuard;
use App\Auth\AuthUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the UMIS service
        $this->app->singleton('App\Services\UMISService', function ($app) {
            return new \App\Services\UMISService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register the custom guard
        Auth::extend('auth_user_provider', function ($app, $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);
            $request = $app['request'];
    
            return new AuthCookieGuard($provider, $request);
        });
    
        // Register the custom provider
        Auth::provider('auth_user_provider', function ($app, array $config) {
            return new AuthUserProvider($config['model']);
        });

        Schema::defaultStringLength(191);
    }
}
