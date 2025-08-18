<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class CheckLogViewerToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();  // Gets the token from the request
        if ($token !== env('LOG_VIEWER_PRODUCTION_TOKEN')) {
            abort(401, 'Unauthorized');  // Deny access if token is wrong
        }
        return $next($request);  // Allow access if token matches
    }
}
