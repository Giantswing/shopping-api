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
            $token = $request->bearerToken();
            $expected = config('app.log_viewer.token');
            return $token === $expected;
        });
    }
}
