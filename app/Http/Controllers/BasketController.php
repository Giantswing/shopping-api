<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AiController;
use App\Models\Basket;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BasketController extends Controller
{
    public function getBasketProducts($slug)
    {
        try {
            $cacheKey = "basket_products_{$slug}";
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return response()->json($cached, 200);
            }

            $basket = Basket::where('slug', $slug)->with('products')->first();

            if (!$basket) {
                return response()->json(['error' => 'basket-not-found'], 404);
            }

            $products = $basket->products;

            $data = [
                'products' => $products,
            ];

            Cache::put($cacheKey, $data, 86400);  // 24 hours

            return response()->json($data, 200);
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
            Log::error('checkIfBasketExists: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
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
            $product = Product::where('name', $params['product'])->where('basket_id', $basket->id)->first();

            if (!$product) {
                $product = Product::create([
                    'name' => $params['product'],
                    'times_added' => 1,
                    'is_added' => true,
                    'quantity' => 1,
                    'basket_id' => $basket->id,
                    'last_added_at' => now(),
                ]);
            } else {
                $product->is_added = true;
                $product->times_added++;
                $product->last_added_at = now();
                $product->quantity = 1;
                $product->save();
            }

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);

            $basket->touch();

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('addProductToBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function editProductQuantity(Request $request, $slug)
    {
        try {
            $params = $request->validate([
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|min:1',
            ]);

            $basket = Basket::where('slug', $slug)->first();
            $product = Product::where('id', $params['product_id'])->where('basket_id', $basket->id)->first();
            if (!$product) {
                return response()->json(['error' => 'product-not-found'], 404);
            }

            $product->quantity = $params['quantity'];
            $product->save();

            $basket->touch();

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('editProductQuantity: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function removeProductFromBasket(Request $request, $slug)
    {
        try {
            $params = $request->validate([
                'product_id' => 'required|integer',
            ]);

            $basket = Basket::where('slug', $slug)->first();
            $product = Product::where('id', $params['product_id'])->where('basket_id', $basket->id)->first();
            if (!$product) {
                return response()->json(['error' => 'product-not-found'], 404);
            }

            $product->is_added = false;
            $product->save();

            $basket->touch();

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('removeProductFromBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function removeAllProductsFromBasket(Request $request, $slug)
    {
        try {
            $basket = Basket::where('slug', $slug)->first();
            $basket->products()->update(['is_added' => false]);

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);

            $basket->touch();

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('removeAllProductsFromBasket: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function removeProductPermanently(Request $request, $slug)
    {
        try {
            $params = $request->validate([
                'product_id' => 'required|integer',
            ]);

            $basket = Basket::where('slug', $slug)->first();
            $product = Product::where('id', $params['product_id'])->where('basket_id', $basket->id)->first();
            if (!$product) {
                return response()->json(['error' => 'product-not-found'], 404);
            }

            $product->delete();

            $basket->touch();

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);

            $response = response()->json([
                'success' => true,
                'products' => $basket->products,
            ], 200);

            return $response;
        } catch (\Exception $e) {
            Log::error('removeProductFromList: ' . 'Message: ' . $e->getMessage() . ' File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }
}
