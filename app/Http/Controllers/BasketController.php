<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use Illuminate\Http\Request;

class BasketController extends Controller
{
    public function getBasketProducts($slug)
    {
        try {
            $basket = Basket::where('slug', $slug)->with('products', 'basketProducts')->first();

            $response = response()->json([
                'basketProducts' => $basket->basketProducts,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkIfBasketExists($slug)
    {
        try {
            $basket = Basket::where('slug', $slug)->first();
            return response()->json(['exists' => $basket ? true : false], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
