<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_numbering_configs', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique();
            $table->string('prefix', 20);
            $table->string('separator', 5)->default('-');
            $table->unsignedTinyInteger('padding')->default(6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_numbering_configs');
    }
};
