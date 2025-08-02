<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('baskets')) {
            return;
        }

        Schema::create('baskets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('The name of the basket');
            $table->string('slug', 255)->comment('The slug of the basket');
            $table->string('password', 255)->comment('The password of the basket');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baskets');
    }
};
