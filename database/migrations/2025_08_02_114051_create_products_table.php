<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('products')) {
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('The name of the product');
            $table->foreignId('basket_id')->constrained('baskets')->onDelete('cascade');
            $table->integer('times_added')->default(0)->comment('The number of times the product has been added to the basket');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
