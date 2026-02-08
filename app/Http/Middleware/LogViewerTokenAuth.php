<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewerTokenAuth
{
    public const COOKIE_NAME = 'log_viewer_token';

    /**
     * When user hits Log Viewer with valid ?token=, set a cookie so subsequent
     * requests (and API calls from the UI) are allowed without token in URL.
     * Cookie works across Octane workers (sent by browser every time).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routePath = config('log-viewer.route_path', 'log-viewer');
        $expected = env('LOG_VIEWER_PRODUCTION_TOKEN') ?? config('app.log_viewer.token');

        $response = $next($request);

        if (
            ! empty($expected)
            && $request->isMethod('GET')
            && $request->path() === $routePath
            && $request->has('token')
            && trim((string) $request->query('token')) === $expected
        ) {
            $response->cookie(
                self::COOKIE_NAME,
                $expected,
                minutes: 60 * 24 * 7, // 7 days
                path: '/',
                secure: $request->secure(),
                httpOnly: true,
                sameSite: 'lax'
            );
        }

        return $response;
    }
}
