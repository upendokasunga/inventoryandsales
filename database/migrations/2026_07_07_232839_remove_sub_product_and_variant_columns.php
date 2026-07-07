<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['parent_product_id']);
            $table->dropColumn([
                'parent_product_id',
                'sub_product_code',
                'has_variants',
                'variant_attributes',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('parent_product_id')->nullable()->after('id')
                ->constrained('products')->cascadeOnDelete();
            $table->string('sub_product_code', 50)->nullable()->after('product_code');
            $table->boolean('has_variants')->default(false)->after('barcode_image');
            $table->json('variant_attributes')->nullable()->after('has_variants');
        });
    }
};
