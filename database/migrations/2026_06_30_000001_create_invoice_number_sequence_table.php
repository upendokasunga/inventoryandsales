<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_number_sequence', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('credit_note_number_sequence', function (Blueprint $table) {
            $table->id();
            $table->year('year')->unique();
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_note_number_sequence');
        Schema::dropIfExists('invoice_number_sequence');
    }
};
