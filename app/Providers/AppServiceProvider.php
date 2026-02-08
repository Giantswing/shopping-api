<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

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

        // Log Viewer: use Gate (config-only for token; env() is unreliable under Octane/config cache)
        Gate::define('viewLogViewer', function ($user = null) {
            $request = request();

            // Allow asset requests without auth
            $path = $request->path();
            if (str_starts_with($path, 'vendor/log-viewer/') ||
                    preg_match('#vendor/log-viewer/.*\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf|eot)$#i', $path)) {
                return true;
            }

            if (app()->environment('local')) {
                return true;
            }

            $expected = config('app.log_viewer.token');
            if ($expected === null || $expected === '') {
                return false;
            }
            $expected = trim((string) $expected);

            $token = trim((string) $request->bearerToken());
            if ($token !== '' && $token === $expected) {
                return true;
            }

            $token = trim((string) $request->query('token'));
            if ($token !== '' && $token === $expected) {
                return true;
            }

            return false;
        });

        // Pass token to Log Viewer view so the front-end can send it on API requests (see published view)
        View::composer('log-viewer::index', function ($view) {
            $request = request();
            $expected = config('app.log_viewer.token');
            if ($expected === null || $expected === '') {
                return;
            }
            $expected = trim((string) $expected);
            $token = trim((string) $request->query('token'));
            if ($token !== '' && $token === $expected) {
                $view->with('log_viewer_bearer_token', $token);
            }
        });
    }
}
