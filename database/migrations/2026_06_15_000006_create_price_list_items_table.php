<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("price_list_items", function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->foreignId("price_list_id")->constrained("price_lists")->cascadeOnDelete();
            $table->foreignId("product_id")->constrained("products")->cascadeOnDelete();
            $table->foreignId("unit_id")->constrained("units")->restrictOnDelete();
            $table->decimal("min_quantity", 15, 3)->default(1);
            $table->decimal("max_quantity", 15, 3)->nullable();
            $table->decimal("price", 15, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(["price_list_id", "product_id", "unit_id", "min_quantity", "max_quantity"], "pli_unique");
            $table->index("product_id");
            $table->index("unit_id");
            $table->index(["price_list_id", "product_id", "unit_id"], "pli_composite");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("price_list_items");
    }
};
