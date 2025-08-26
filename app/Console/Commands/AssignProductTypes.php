<?php

namespace App\Console\Commands;

use App\Http\Controllers\AiController;
use App\Models\Basket;
use App\Models\Product;
use App\Models\Type;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class AssignProductTypes extends Command
{
    protected $signature = 'command:assign-product-types {--force : Assign types to all products, even those that already have a type}';

    protected $description = 'Assign product types to products';

    public function handle()
    {
        $this->info('Assigning product types to products');

        $force = $this->option('force');

        $products = $force
            ? Product::all()
            : Product::whereNull('type')->get();

        $baskets = Basket::all();
        foreach ($baskets as $basket) {
            Cache::forget('basket_products_' . $basket->slug);
        }

        if ($force) {
            $types = Type::all();
            foreach ($types as $type) {
                $type->delete();
            }
            $this->info('All types deleted');
        }

        foreach ($products as $product) {
            try {
                $aiController = new AiController();
                $aiController->getProductType($product->id, $force);

                $this->info('Product type assigned for product: ' . $product->name . ' - ' . $product->refresh()->type);
            } catch (\Exception $e) {
                $this->error('Error assigning product type for product: ' . $product->name . ' - ' . $e->getMessage());
            }
        }

        $this->info('Product types assigned successfully');
    }
}
