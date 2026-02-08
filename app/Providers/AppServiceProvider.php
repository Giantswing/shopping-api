<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
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
        // Force root URL in production so asset() uses APP_URL (fixes proxies/Octane)
        if (! app()->environment('local')) {
            $appUrl = config('app.url');
            if (! empty($appUrl)) {
                URL::forceRootUrl($appUrl);
            }
        }

        LogViewer::auth(function ($request) {
            // Allow asset requests (CSS, JS, images) without authentication
            $path = $request->path();
            if (str_starts_with($path, 'vendor/log-viewer/') ||
                    preg_match('#vendor/log-viewer/.*\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$#i', $path)) {
                return true;
            }

            // Allow access in local environment without token
            if (app()->environment('local')) {
                return true;
            }

            // Read from env first so it works even when config is cached
            $expected = env('LOG_VIEWER_PRODUCTION_TOKEN') ?? config('app.log_viewer.token');

            // In production, require a token to be configured; otherwise deny
            if (empty($expected)) {
                return false;
            }

            // Allow Bearer token (API or when front-end sends it)
            if (trim((string) $request->bearerToken()) === $expected) {
                return true;
            }

            // Allow query param ?token= (page load with token in URL)
            if (trim((string) $request->query('token')) === $expected) {
                return true;
            }

            // Allow cookie (set by middleware when user visits with ?token=)
            if (trim((string) $request->cookie('log_viewer_token')) === $expected) {
                return true;
            }

            return false;
        });
    }
}
