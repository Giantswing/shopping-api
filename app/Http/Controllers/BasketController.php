<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AiController;
use App\Models\Basket;
use App\Models\Product;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Services\LogHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class BasketController extends Controller
{
    public function getBasketProducts($slug)
    {
        try {
            // sleep(2);

            $cacheKey = "basket_products_{$slug}";
            $cached = Cache::get($cacheKey);

            if ($cached) {
                return response()->json($cached, 200);
            }

            $basket = Basket::where('slug', $slug)->with('products')->first();

            if (!$basket) {
                LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Basket not found for slug: ' . $slug);
                return response()->json(['error' => 'basket-not-found'], 404);
            }

            $products = $basket->products;

            $data = [
                'products' => $products,
            ];

            Cache::put($cacheKey, $data, 86400);  // 24 hours

            return response()->json($data, 200);
        } catch (\Exception $e) {
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Error getting basket products: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateBasket(Request $request, $slug)
    {
        try {
            // sleep(1);

            $params = $request->validate([
                'products' => 'required|array',
                'products.*.name' => 'required|string',
                'products.*.is_added' => 'required|boolean',
                'products.*.quantity' => 'required|integer|min:0',
                'products.*.id' => 'nullable|integer',
            ]);

            $basket = Basket::where('slug', $slug)->first();
            if (!$basket) {
                LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Basket not found for slug: ' . $slug);
                return response()->json(['error' => 'basket-not-found'], 404);
            }

            $updatedProducts = [];
            foreach ($params['products'] as $item) {
                $product = null;
                if (!empty($item['id'])) {
                    $product = Product::where('id', $item['id'])->where('basket_id', $basket->id)->first();
                }
                if (!$product) {
                    $product = Product::where('name', $item['name'])->where('basket_id', $basket->id)->first();
                }
                if (!$product) {
                    $product = Product::create([
                        'name' => $item['name'],
                        'basket_id' => $basket->id,
                        'times_added' => $item['is_added'] ? 1 : 0,
                        'is_added' => $item['is_added'],
                        'quantity' => max(1, $item['quantity']),
                        'last_added_at' => $item['is_added'] ? now() : null,
                    ]);
                } else {
                    $product->is_added = $item['is_added'];
                    $product->quantity = max(1, $item['quantity']);
                    if ($item['is_added']) {
                        $product->times_added = ($product->times_added ?? 0) + 1;
                        $product->last_added_at = now();
                    }
                    $product->save();
                }
                $updatedProducts[] = $product;
            }

            // Set is_added = false for any basket product not in the synced list (including newly created)
            $keepIds = array_column($updatedProducts, 'id');
            $basket->products()->whereNotIn('id', $keepIds)->update(['is_added' => false]);

            $cacheKey = "basket_products_{$slug}";
            Cache::forget($cacheKey);
            $basket->touch();

            $products = $basket->fresh()->products;

            return response()->json([
                'success' => true,
                'products' => $products,
            ], 200);
        } catch (\Exception $e) {
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Error updating basket: ' . $e->getMessage());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }

    public function checkIfBasketExists($slug)
    {
        try {
            $basket = Basket::where('slug', $slug)->first();
            return response()->json(['exists' => $basket ? true : false, 'name' => $basket->name ?? null], 200);
        } catch (\Exception $e) {
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, $e->getMessage());
            return response()->json(['error' => 'internal-server-error'], 500);
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

            if (! Hash::check($params['password'], $basket->password)) {
                LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Invalid password for basket ' . $params['slug']);
                return response()->json(['error' => 'invalid-password'], 401);
            }

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, $e->getMessage());
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
                LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Basket already exists for slug: ' . $slug);
                return response()->json(['error' => 'basket-already-exists'], 400);
            }

            $basket = Basket::create([
                'name' => $params['name'],
                'slug' => $slug,
                'password' => Hash::make($params['password']),
            ]);

            LogHelper::info(__CLASS__, __FUNCTION__, __LINE__, 'Basket created with slug: ' . $slug);

            return response()->json(['success' => true, 'slug' => $slug], 200);
        } catch (\Exception $e) {
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Error creating basket: ' . $e->getMessage());
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
                LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Product not found for basket ' . $slug . ' and product id ' . $params['product_id']);
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
            LogHelper::error(__CLASS__, __FUNCTION__, __LINE__, 'Error removing product permanently: ' . $e->getMessage());
            return response()->json(['error' => 'internal-server-error'], 500);
        }
    }
}
