<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('name')->unique();
            $table->json('permissions');
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->boolean('is_active')->default(true)->after('password');
        });

        $now = now();
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'Administrator',
            'permissions' => json_encode(['*'], JSON_THROW_ON_ERROR),
            'is_system' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('users')->whereNull('role_id')->update(['role_id' => $roleId]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn('is_active');
        });

        Schema::dropIfExists('roles');
    }
};
