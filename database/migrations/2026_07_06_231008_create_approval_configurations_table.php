<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('module_key', 50)->unique(); // sales, po, store_request, stock_transfer, expense
            $table->string('module_name', 255);
            $table->unsignedTinyInteger('approval_level')->default(0); // 0-3
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_configuration_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('level'); // 1, 2, 3
            $table->string('name', 255); // e.g., "Store Manager", "Finance Manager"
            $table->foreignId('group_id')->constrained('groups')->restrictOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['approval_configuration_id', 'level'], 'approval_level_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_levels');
        Schema::dropIfExists('approval_configurations');
    }
};
