<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('branch');
            $table->string('swift_code');
            $table->timestamps();

            $table->unique(['name', 'branch', 'swift_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
