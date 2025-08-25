<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_added')->default(false)->after('basket_id');
            $table->integer('quantity')->default(1)->after('is_added');

            $table->index('is_added');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_added');
            $table->dropColumn('quantity');

            $table->dropIndex('is_added');
        });
    }
};
