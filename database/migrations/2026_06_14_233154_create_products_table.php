<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku', 100)->unique();
            $table->string('barcode', 100)->unique()->nullable();
            $table->string('barcode_image')->nullable();
            $table->text('description')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->boolean('tax_inclusive')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('track_stock')->default(true);
            $table->decimal('reorder_level', 15, 3)->default(0);
            $table->string('image')->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_factor', 15, 3);
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('wholesale_price', 15, 2)->nullable();
            $table->decimal('bulk_price', 15, 2)->nullable();
            $table->boolean('is_default_sale')->default(false);
            $table->boolean('is_default_purchase')->default(false);
            $table->string('barcode', 100)->unique()->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_units');
        Schema::dropIfExists('products');
    }
};
