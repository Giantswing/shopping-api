<?php

use App\Http\Controllers\BasketController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::get('/basket/check-if-basket-exists/{slug}', [BasketController::class, 'checkIfBasketExists']);
Route::get('/basket/{slug}', [BasketController::class, 'getBasketProducts']);
Route::post('/basket/connect', [BasketController::class, 'connectToBasket']);
Route::post('/basket/create', [BasketController::class, 'createBasket']);
