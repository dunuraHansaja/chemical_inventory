<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'type',
        'cas_number',
        'category_id',
        'product_category_id',
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

    public function isProduct(): bool
    {
        return ($this->type ?? 'material') === 'product' || $this->product_category_id !== null;
    }

    public function isMaterial(): bool
    {
        return ! $this->isProduct();
    }

    public function scopeRawMaterials(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('materials.type', 'material')
              ->whereNull('materials.product_category_id');
        });
    }

    public function scopeProductionProducts(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('materials.type', 'product')
              ->orWhereNotNull('materials.product_category_id');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
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

    public function recipes(): HasMany
    {
        return $this->hasMany(ProductRecipe::class, 'product_id');
    }
}
