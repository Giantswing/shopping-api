<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Log Viewer assets (production only: served by Laravel when static serving fails)
| On local, the server serves public/ directly so this route is not used.
|--------------------------------------------------------------------------
*/
if (! app()->environment('local')) {
    Route::get('/vendor/log-viewer/{path}', function (string $path) {
    $path = str_replace('..', '', $path);
    $base = public_path('vendor/log-viewer');
    $fullPath = realpath($base . '/' . $path);

    if ($fullPath === false || ! str_starts_with($fullPath, realpath($base))) {
        abort(404);
    }

    $mimes = [
        'js' => 'application/javascript',
        'css' => 'text/css',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject',
    ];
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $mime = $mimes[$ext] ?? 'application/octet-stream';

    return response()->file($fullPath, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->where('path', '.+');
}
