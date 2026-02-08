<?php

use App\Http\Controllers\AiController;
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
    Route::get('/ai/get-product-type/{productId}', [AiController::class, 'getProductType']);
    Route::get('/basket/{slug}', [BasketController::class, 'getBasketProducts']);
    Route::put('/basket/{slug}', [BasketController::class, 'updateBasket']);
    Route::post('/basket/{slug}/remove-product-permanently', [BasketController::class, 'removeProductPermanently']);
});
