<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Closure;

class CheckLogViewerToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $expected = env('LOG_VIEWER_PRODUCTION_TOKEN');
        Log::debug('Log Viewer Token Check', [
            'provided' => $token,
            'expected' => $expected,
            'env_loaded' => env('LOG_VIEWER_PRODUCTION_TOKEN', 'not_set'),
        ]);
        if ($token !== $expected) {
            Log::error('Log Viewer token mismatch');
            abort(401, 'Unauthorized');
        }
        return $next($request);
    }
}
