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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name', 191)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->foreignId('product_category_id')
                ->nullable()
                ->after('category_id')
                ->constrained('product_categories')
                ->nullOnDelete();
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->foreignId('product_category_id')
                ->nullable()
                ->after('created_by')
                ->constrained('product_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
        });

        Schema::dropIfExists('product_categories');
    }
};
