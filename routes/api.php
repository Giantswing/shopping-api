<?php

use App\Http\Controllers\BasketController;
use App\Http\Middleware\CheckBasketPassword;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/basket/check-if-basket-exists/{slug}', [BasketController::class, 'checkIfBasketExists']);
Route::post('/basket/connect', [BasketController::class, 'connectToBasket']);
Route::post('/basket/create', [BasketController::class, 'createBasket']);

Route::middleware([CheckBasketPassword::class])->group(function () {
    Route::get('/basket/{slug}', [BasketController::class, 'getBasketProducts']);
});
