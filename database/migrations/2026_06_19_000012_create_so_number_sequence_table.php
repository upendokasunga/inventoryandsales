<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('so_number_sequence', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('so_number_sequence');
    }
};
