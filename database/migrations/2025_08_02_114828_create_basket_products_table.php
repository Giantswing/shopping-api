<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('basket_products')) {
            return;
        }

        Schema::create('basket_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('basket_id')->constrained('baskets')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(0)->comment('The quantity of the product in the basket');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('basket_products');
    }
};
