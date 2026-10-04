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
        Schema::create('material_lots', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->foreignId('material_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('lot_number', 100);

            $table->date('manufactured_date')->nullable();

            $table->date('expiry_date')->nullable();

            $table->decimal('quantity_received', 15, 3)->default(0);

            $table->decimal('quantity_on_hand', 15, 3)->default(0);

            $table->decimal('unit_cost', 15, 2)->nullable();

            $table->string('supplier_lot_number')->nullable();

            $table->string('storage_location')->nullable();

            $table->string('status', 30)->default('active');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique([
                'material_id',
                'lot_number'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_lots');
    }
};
