<?php

namespace Database\Seeders;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\ProductCategory;
use App\Models\ProductRecipe;
use App\Models\ProductionOrder;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionMockupSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $userId = $user ? $user->id : 1;

        $cats = [
            'Category 1' => [
                ['name' => 'Hand Sanitizer', 'code' => 'HS-001', 'date' => '2024-10-15', 'qty' => 120, 'unit' => 'L', 'color' => 'red'],
                ['name' => 'Disinfectant Cleaner', 'code' => 'DC-002', 'date' => '2024-09-28', 'qty' => 75, 'unit' => 'L', 'color' => 'green'],
                ['name' => 'Surface Wipe Solution', 'code' => 'SW-003', 'date' => '2024-09-12', 'qty' => 200, 'unit' => 'L', 'color' => 'blue'],
                ['name' => 'Glass Cleaner', 'code' => 'GC-004', 'date' => '2024-08-30', 'qty' => 50, 'unit' => 'L', 'color' => 'orange'],
            ],
            'Category 2' => [
                ['name' => 'Bleach Solution', 'code' => 'BL-001', 'date' => '2024-10-01', 'qty' => 500, 'unit' => 'L', 'color' => 'green'],
                ['name' => 'Floor Cleaner', 'code' => 'FC-002', 'date' => '2024-09-20', 'qty' => 250, 'unit' => 'L', 'color' => 'orange'],
                ['name' => 'Toilet Cleaner', 'code' => 'TC-003', 'date' => '2024-09-05', 'qty' => 100, 'unit' => 'L', 'color' => 'red'],
                ['name' => 'Multi-Purpose Cleaner', 'code' => 'MPC-004', 'date' => '2024-08-25', 'qty' => 300, 'unit' => 'L', 'color' => 'purple'],
                ['name' => 'Air Freshener', 'code' => 'AF-005', 'date' => '2024-08-10', 'qty' => 150, 'unit' => 'L', 'color' => 'orange'],
            ],
            'Category 3' => [
                ['name' => 'Detergent', 'code' => 'DT-001', 'date' => '2024-10-14', 'qty' => 400, 'unit' => 'L', 'color' => 'orange'],
                ['name' => 'Fabric Softener', 'code' => 'FS-002', 'date' => '2024-08-18', 'qty' => 220, 'unit' => 'L', 'color' => 'purple'],
            ],
        ];

        // Raw materials / ingredients
        $raws = [
            ['name' => 'Sodium Hydroxide', 'code' => 'RAW-SH-01', 'unit' => 'kg', 'stock' => 500],
            ['name' => 'Water', 'code' => 'RAW-H2O-01', 'unit' => 'L', 'stock' => 10000],
            ['name' => 'Fragrance', 'code' => 'RAW-FRG-01', 'unit' => 'mL', 'stock' => 5000],
        ];
        foreach ($raws as $r) {
            $m = Material::firstOrCreate(['item_code' => $r['code']], [
                'name' => $r['name'],
                'stock_unit' => $r['unit'],
                'reorder_level' => 10,
                'is_active' => true,
            ]);
            MaterialLot::firstOrCreate(['material_id' => $m->id, 'lot_number' => 'LOT-' . $r['code']], [
                'quantity_received' => $r['stock'],
                'quantity_on_hand' => $r['stock'],
                'status' => 'active',
            ]);
        }

        $i = 100;
        foreach ($cats as $catName => $products) {
            $pCat = ProductCategory::firstOrCreate(['name' => $catName]);
            foreach ($products as $p) {
                $m = Material::firstOrCreate(['item_code' => $p['code']], [
                    'name' => $p['name'],
                    'stock_unit' => $p['unit'],
                    'product_category_id' => $pCat->id,
                    'reorder_level' => 0,
                    'is_active' => true,
                ]);
                $m->update(['product_category_id' => $pCat->id]);

                $lotNo = 'PB-2024-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT);
                $order = ProductionOrder::firstOrCreate(['order_number' => $lotNo], [
                    'produced_at' => $p['date'],
                    'product_category_id' => $pCat->id,
                    'status' => 'completed',
                    'created_by' => $userId,
                ]);

                $lot = MaterialLot::firstOrCreate(['material_id' => $m->id, 'lot_number' => $lotNo], [
                    'quantity_received' => $p['qty'],
                    'quantity_on_hand' => $p['qty'],
                    'status' => 'active',
                ]);

                if (! StockMovement::where('reference_id', $order->id)->where('movement_type', 'production_output')->exists()) {
                    StockMovement::create([
                        'material_id' => $m->id,
                        'material_lot_id' => $lot->id,
                        'movement_type' => 'production_output',
                        'quantity' => $p['qty'],
                        'balance_after' => $p['qty'],
                        'reference_type' => 'production',
                        'reference_id' => $order->id,
                        'notes' => 'Production batch ' . $lotNo,
                        'created_by' => $userId,
                        'created_at' => $p['date'] . ' 10:00:00',
                    ]);
                }

                $water = Material::where('item_code', 'RAW-H2O-01')->first();
                $fragrance = Material::where('item_code', 'RAW-FRG-01')->first();
                $sles = Material::where('item_code', '0001')->orWhere('name', 'like', '%SLES%')->first();
                if ($water && $fragrance && ! ProductRecipe::where('product_id', $m->id)->exists()) {
                    ProductRecipe::create([
                        'product_id' => $m->id,
                        'material_id' => $water->id,
                        'quantity' => max(1, round($p['qty'] * 0.75, 2)),
                    ]);
                    ProductRecipe::create([
                        'product_id' => $m->id,
                        'material_id' => $fragrance->id,
                        'quantity' => 5,
                    ]);
                    if ($sles) {
                        ProductRecipe::create([
                            'product_id' => $m->id,
                            'material_id' => $sles->id,
                            'quantity' => max(1, round($p['qty'] * 0.15, 2)),
                        ]);
                    }
                }

                $i++;
            }
        }
    }
}
