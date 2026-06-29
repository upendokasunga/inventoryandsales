<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_number_sequence', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->string('type', 10);
            $table->unsignedBigInteger('last_number')->default(0);
            $table->unique(['year', 'type']);
            $table->timestamps();
        });

        DB::table('return_number_sequence')->insert([
            ['year' => now()->year, 'type' => 'SR', 'last_number' => 0],
            ['year' => now()->year, 'type' => 'PR', 'last_number' => 0],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('return_number_sequence');
    }
};
