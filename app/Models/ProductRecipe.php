<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProductRecipe links a finished product (material) to a raw material and required quantity.
 */
class ProductRecipe extends Model
{
    protected $table = 'product_recipes';

    protected $fillable = [
        'product_id',
        'material_id',
        'quantity',
    ];

    /**
     * The finished product (treated as a Material model).
     */
    public function product()
    {
        return $this->belongsTo(Material::class, 'product_id');
    }

    /**
     * The raw material required for the product.
     */
    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}

