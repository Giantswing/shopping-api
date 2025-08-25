<?php

namespace App\Console\Commands;

use App\Models\Basket;
use App\Models\BasketProduct;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class MigrateBasketProducts extends Command
{
    protected $signature = 'command:migrate-basket-products';

    protected $description = 'Migrate basket products from basket_products table to products table (using the is_added and quantity columns)';

    public function handle()
    {
        $this->info('Migrating basket products from basket_products table to products table (using the is_added and quantity columns)');

        // Reset is_added and quantity for all products
        $this->info('Resetting is_added and quantity columns for all products');
        Product::query()->update(['is_added' => false, 'quantity' => 1]);

        $baskets = Basket::all();
        foreach ($baskets as $basket) {
            Cache::forget('basket_products_' . $basket->slug);

            $this->info('Migrating basket products for basket: ' . $basket->name);

            $basketProducts = BasketProduct::where('basket_id', $basket->id)->get();
            foreach ($basketProducts as $basketProduct) {
                $product = Product::where('id', $basketProduct->product_id)
                    ->where('basket_id', $basket->id)
                    ->first();

                if (!$product) {
                    $this->error('Product not found for basket: ' . $basket->name . ' and product: ' . $basketProduct->product_id);
                    continue;
                }

                $product->is_added = true;
                $product->quantity = $basketProduct->quantity;
                $product->save();
            }
        }

        $this->info('Migration completed');
    }
}
