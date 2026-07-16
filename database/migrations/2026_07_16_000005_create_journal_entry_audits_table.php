<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id')->index();
            $table->string('action', 50);
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('metadata');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_audits');
    }
};
