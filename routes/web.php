<?php

use Illuminate\Support\Facades\Route;

Route::get('/log-viewer', [\Opcodes\LogViewer\Http\Controllers\LogViewerController::class, 'index'])
    ->middleware('check.logviewer');
