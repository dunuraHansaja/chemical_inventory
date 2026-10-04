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
        Schema::create('materials', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->id();

            $table->string('item_code', 50)->unique();
            $table->string('name');

            $table->string('cas_number', 50)->nullable()->index();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('stock_unit', 20)->default('kg');

            $table->decimal('reorder_level', 15, 3)->default(0);

            $table->decimal('cost_per_unit', 15, 2)->nullable();

            $table->string('storage_location')->nullable();

            $table->foreignId('preferred_supplier_id')
                ->nullable()
                ->constrained('suppliers')
                ->nullOnDelete();

            $table->json('hazard_classes')->nullable();

            $table->text('handling_notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
