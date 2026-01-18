<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Opcodes\LogViewer\Facades\LogViewer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        LogViewer::auth(function ($request) {
            // Allow access in local environment without token
            if (app()->environment('local')) {
                return true;
            }

            // Check for bearer token
            $token = $request->bearerToken();
            $expected = config('app.log_viewer.token');

            // If no token is configured, allow access
            if (empty($expected)) {
                return true;
            }

            // Verify token matches
            return $token === $expected;
        });
    }
}
