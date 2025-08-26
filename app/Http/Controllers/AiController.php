<?php

namespace App\Http\Controllers;

use App\Models\Basket;
use App\Models\Product;
use App\Models\Type;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AiController extends Controller
{
    public function getProductType(string $productId, bool $force = false)
    {
        try {
            $product = Product::find($productId);
            if (!$product) {
                return response()->json(['type' => 'uncategorized'], 200);
            }

            $basket = Basket::find($product->basket_id);
            if (!$basket) {
                return response()->json(['type' => 'uncategorized'], 200);
            }

            Cache::forget("basket_products_{$basket->slug}");

            if (!$force) {
                $type = Type::where('product_name', $product->name)->first();
                if ($type) {
                    $product->type = $type->type;
                    $product->save();
                    return response()->json(['type' => $type->type], 200);
                }
            }

            $client = $this->getAIClient();
            if (!$client) {
                $product->type = 'uncategorized';
                $product->save();
                return response()->json(['type' => 'uncategorized'], 200);
            }

            $categories = [
                'oils_spices_sauces',
                'water_and_soft_drinks',
                'snacks_and_sweets',
                'rice_pulses_pasta',
                'baby',
                'alcohol',
                'coffee_and_tea',
                'meat',
                'deli_and_cheese',
                'frozen',
                'canned_and_soups',
                'personal_care',
                'pharmacy',
                'fruit_and_vegetables',
                'dairy_and_eggs',
                'cleaning_and_home',
                'seafood_and_fish',
                'pets',
                'bakery_and_pastry',
                'prepared_food',
            ];

            $categoriesStr = implode(', ', $categories);

            $prompt = "Categorize the grocery product \"$product->name\" into ONE of the following: $categoriesStr. Respond ONLY with the category name.";
            Log::info('Prompt is ' . $prompt);

            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-4.1-nano',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 10
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (isset($data['choices'][0]['message']['content'])) {
                $category = trim(strtolower($data['choices'][0]['message']['content']));
                $finalCategory = in_array($category, $categories) ? $category : 'uncategorized';

                $product->type = $finalCategory;
                $product->save();

                Type::create([
                    'product_name' => $product->name,
                    'type' => $finalCategory,
                ]);

                return response()->json(['type' => $finalCategory], 200);
            }

            $product->type = 'uncategorized';
            $product->save();

            return response()->json(['type' => 'uncategorized'], 200);
        } catch (\Exception $e) {
            Log::error('getProductType: ' . $e->getMessage());
            $product->type = 'uncategorized';
            $product->save();
            return response()->json(['type' => 'uncategorized'], 200);
        }
    }

    private function getAIClient()
    {
        try {
            $openai_key = config('app.openai_api_key');
            if (!$openai_key) {
                Log::error('OpenAI API key missing');
                return null;
            }

            return new Client([
                'base_uri' => 'https://api.openai.com/v1/',
                'headers' => [
                    'Authorization' => 'Bearer ' . $openai_key,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30.0,
            ]);
        } catch (\Exception $e) {
            Log::error('getAIClient: ' . $e->getMessage());
            return null;
        }
    }
}
