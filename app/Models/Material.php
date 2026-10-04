<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'item_code',
        'name',
        'cas_number',
        'category_id',
        'stock_unit',
        'reorder_level',
        'cost_per_unit',
        'storage_location',
        'preferred_supplier_id',
        'hazard_classes',
        'handling_notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'hazard_classes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    public function lots(): HasMany
    {
        return $this->hasMany(MaterialLot::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function productionOrderItems(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }
}
