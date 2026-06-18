<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("price_lists", function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->unique();
            $table->string("name");
            $table->text("description")->nullable();
            $table->foreignId("customer_group_id")->nullable()->constrained("customer_groups")->nullOnDelete();
            $table->string("currency", 3)->default("TZS");
            $table->boolean("is_active")->default(true);
            $table->dateTime("valid_from")->nullable();
            $table->dateTime("valid_until")->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index("is_active");
            $table->index("valid_from");
            $table->index("valid_until");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("price_lists");
    }
};
