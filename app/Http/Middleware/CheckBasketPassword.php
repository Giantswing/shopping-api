<?php

namespace App\Http\Middleware;

use App\Models\Basket;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Closure;

class CheckBasketPassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $basket = Basket::where('slug', $request->header('X-Basket-Slug'))->first();
        if (!$basket) {
            return response()->json(['error' => 'basket-not-found'], 404);
        }

        if (!\Hash::check($request->header('X-Basket-Password'), $basket->password)) {
            return response()->json(['error' => 'invalid-password'], 401);
        }

        return $next($request);
    }
}
