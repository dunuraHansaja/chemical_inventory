<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_recipes', function (Blueprint $table) {
            $table->id();
            // The finished product, referencing a material that is marked as a product
            $table->foreignId('product_id')
                ->constrained('materials')
                ->onDelete('cascade');
            // Raw material required for the product
            $table->foreignId('material_id')
                ->constrained('materials')
                ->onDelete('cascade');
            // Quantity of material needed per unit of product
            $table->decimal('quantity', 12, 2);
            $table->timestamps();

            $table->unique(['product_id', 'material_id'], 'product_material_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_recipes');
    }
};

