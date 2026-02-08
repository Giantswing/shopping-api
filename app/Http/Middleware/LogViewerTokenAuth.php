<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewerTokenAuth
{
    /**
     * If user hits Log Viewer with ?token=xxx and token is valid, set session so
     * subsequent requests (and API calls from the UI) are allowed. We don't redirect
     * so the session is saved with this response and works with Octane/multi-worker.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routePath = config('log-viewer.route_path', 'log-viewer');

        if (
            $request->isMethod('GET')
            && $request->path() === $routePath
            && $request->has('token')
        ) {
            $expected = env('LOG_VIEWER_PRODUCTION_TOKEN') ?? config('app.log_viewer.token');
            if (! empty($expected) && $request->query('token') === $expected) {
                $request->session()->put('log_viewer_authenticated', true);
            }
        }

        return $next($request);
    }
}
