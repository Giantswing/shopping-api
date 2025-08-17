<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('products', 'last_added_at')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('last_added_at')->after('times_added')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('last_added_at');
        });
    }
};
