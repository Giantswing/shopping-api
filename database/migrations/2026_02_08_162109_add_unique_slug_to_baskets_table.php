<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find slugs that have more than one basket
        $duplicateSlugs = DB::table('baskets')
            ->select('slug')
            ->groupBy('slug')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('slug');

        foreach ($duplicateSlugs as $slug) {
            // Get basket ids for this slug with their product count, ordered by count descending
            $basketIdsToKeep = DB::table('baskets')
                ->leftJoin('products', 'baskets.id', '=', 'products.basket_id')
                ->where('baskets.slug', $slug)
                ->select('baskets.id')
                ->groupBy('baskets.id')
                ->orderByRaw('COUNT(products.id) DESC')
                ->pluck('id');

            // Keep the first (highest product count), delete the rest
            $keepId = $basketIdsToKeep->first();
            $deleteIds = $basketIdsToKeep->slice(1)->values()->all();

            if (! empty($deleteIds)) {
                DB::table('baskets')->whereIn('id', $deleteIds)->delete();
            }
        }

        Schema::table('baskets', function (Blueprint $table) {
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baskets', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });
    }
};
