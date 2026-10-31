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
        Schema::table('materials', function (Blueprint $table) {
            $table->string('type', 20)->default('material')->after('name')->index();
        });

        // Classify existing materials that have product_category_id or recipe formulation as 'product'
        $productIds = DB::table('materials')
            ->whereNotNull('product_category_id')
            ->pluck('id')
            ->toArray();

        if (Schema::hasTable('product_recipes')) {
            $recipeProductIds = DB::table('product_recipes')->pluck('product_id')->toArray();
            $productIds = array_unique(array_merge($productIds, $recipeProductIds));
        }

        if (Schema::hasTable('stock_movements')) {
            $productionOutputIds = DB::table('stock_movements')
                ->where('movement_type', 'production_output')
                ->pluck('material_id')
                ->toArray();
            $productIds = array_unique(array_merge($productIds, $productionOutputIds));
        }

        if (! empty($productIds)) {
            DB::table('materials')->whereIn('id', $productIds)->update(['type' => 'product']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
