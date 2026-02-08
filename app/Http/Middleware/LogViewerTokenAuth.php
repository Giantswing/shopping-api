<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogViewerTokenAuth
{
    /**
     * If user hits Log Viewer with ?token=xxx and token is valid, set session and redirect
     * so the token is not left in the URL.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $routePath = config('log-viewer.route_path', 'log-viewer');

        if (
            $request->isMethod('GET')
            && $request->path() === $routePath
            && $request->has('token')
        ) {
            $expected = config('app.log_viewer.token');
            if (! empty($expected) && $request->query('token') === $expected) {
                $request->session()->put('log_viewer_authenticated', true);
                return redirect()->to($request->url());
            }
        }

        return $next($request);
    }
}
