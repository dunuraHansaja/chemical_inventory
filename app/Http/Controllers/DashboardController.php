<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now()->toDateString();

        return view('dashboard', [
            'materialCount' => Material::query()->where('is_active', true)->count(),
            'supplierCount' => Supplier::query()->where('is_active', true)->count(),
            'lowStockCount' => DB::table('materials')
                ->whereNull('materials.deleted_at')
                ->where('materials.is_active', true)
                ->whereRaw('(select coalesce(sum(quantity_on_hand), 0) from material_lots where material_lots.material_id = materials.id) <= materials.reorder_level')
                ->count(),
            'expiryLots' => MaterialLot::query()
                ->with('material')
                ->whereHas('material')
                ->where('quantity_on_hand', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [$today, now()->addDays(90)->toDateString()])
                ->orderBy('expiry_date')
                ->limit(6)
                ->get(),
            'recentMovements' => StockMovement::query()
                ->with(['material', 'createdBy'])
                ->latest()
                ->limit(8)
                ->get(),
            'openPurchases' => PurchaseOrder::query()->where('status', 'draft')->count(),
            'stockValue' => DB::table('material_lots')
                ->selectRaw('COALESCE(SUM(quantity_on_hand * COALESCE(unit_cost, 0)), 0) as total')
                ->value('total'),
        ]);
    }
}
