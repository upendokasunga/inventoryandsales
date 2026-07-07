<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code', 50)->nullable()->unique()->after('id');
            $table->string('sub_product_code', 50)->nullable()->after('product_code');
            $table->string('product_id', 50)->nullable()->unique()->after('sub_product_code');
            $table->string('product_type', 20)->default('goods')->after('name');
            $table->string('material_type', 30)->default('sale')->after('product_type');
            $table->decimal('price', 15, 2)->default(0)->after('material_type');
            $table->decimal('retail_price', 15, 2)->nullable()->after('price');
            $table->decimal('standard_cost', 15, 2)->nullable()->after('retail_price');
            $table->string('costing_method', 20)->nullable()->after('standard_cost');
            $table->foreignId('income_account_id')->nullable()->after('costing_method')
                ->constrained('accounts')->nullOnDelete();
            $table->string('cost_center', 255)->nullable()->after('income_account_id');
            $table->string('brand_code', 100)->nullable()->after('cost_center');
            $table->date('expiry_date')->nullable()->after('brand_code');
            $table->string('unit', 20)->default('pc')->after('expiry_date');
            $table->string('category', 255)->nullable()->after('unit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'product_code', 'sub_product_code', 'product_id',
                'product_type', 'material_type', 'price', 'retail_price',
                'standard_cost', 'costing_method', 'income_account_id',
                'cost_center', 'brand_code',
                'expiry_date', 'unit', 'category',
            ]);
        });
    }
};
