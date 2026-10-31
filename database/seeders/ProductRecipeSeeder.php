<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: product material id 1 uses material 2 (quantity 5) and material 3 (quantity 2)
        DB::table('product_recipes')->insert([
            ['product_id' => 1, 'material_id' => 2, 'quantity' => 5.00, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 1, 'material_id' => 3, 'quantity' => 2.00, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
