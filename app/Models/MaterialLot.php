<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialLot extends Model
{
    protected $fillable = [
        'material_id',
        'lot_number',
        'manufactured_date',
        'expiry_date',
        'quantity_received',
        'quantity_on_hand',
        'unit_cost',
        'supplier_lot_number',
        'storage_location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'manufactured_date' => 'date',
            'expiry_date' => 'date',
            'quantity_received' => 'decimal:3',
            'quantity_on_hand' => 'decimal:3',
            'unit_cost' => 'decimal:2',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
