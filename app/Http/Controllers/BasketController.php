<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\BasketProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
            return response()->json(['exists' => $basket ? true : false, 'name' => $basket->name ?? null], 200);
        } catch (\Exception $e) {
            Log::error('getBasketProducts: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function connectToBasket(Request $request)
    {
        try {
            $params = $request->validate([
                'slug' => 'required|string',
                'password' => 'required|string',
            ]);

            $basket = Basket::where('slug', $params['slug'])->first();
            if (!$basket) {
                return response()->json(['error' => 'basket-not-found'], 404);
            }

            if (!\Hash::check($params['password'], $basket->password)) {
                return response()->json(['error' => 'invalid-password'], 401);
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            Log::error('connectBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function createBasket(Request $request)
    {
        try {
            $params = $request->validate([
                'name' => 'required|string',
                'password' => 'required|string',
            ]);

            $slug = Str::slug($params['name']);

            $basket = Basket::where('slug', $slug)->first();
            if ($basket) {
                return response()->json(['error' => 'basket-already-exists'], 400);
            }

            $basket = Basket::create([
                'name' => $params['name'],
                'slug' => $slug,
                'password' => \Hash::make($params['password']),
            ]);

            return response()->json(['success' => true, 'slug' => $slug], 200);
        } catch (\Exception $e) {
            Log::error('createBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function addProductToBasket(Request $request, $slug)
    {
        try {
            $params = $request->validate([
                'product' => 'required|string',
            ]);

            $basket = Basket::where('slug', $slug)->first();
            $product = Product::where('name', $params['product'])->first();

            if (!$product) {
                $product = Product::create([
                    'name' => $params['product'],
                    'times_added' => 1,
                    'basket_id' => $basket->id,
                ]);
            } else {
                $product->times_added++;
                $product->save();
            }

            $basketProduct = BasketProduct::create([
                'basket_id' => $basket->id,
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
                'basketProducts' => $basket->basketProducts,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('addProductToBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }
}
