<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_tracking', function (Blueprint $table) {
            $table->id();
            $table->morphs('approvable');
            $table->foreignId('approval_configuration_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('current_level')->default(0);
            $table->unsignedTinyInteger('required_levels')->default(1);
            $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('approval_tracking_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_tracking_id')->constrained('approval_tracking')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->string('action'); // submitted, approved, rejected, level_completed, cancelled
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('comments')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_tracking_log');
        Schema::dropIfExists('approval_tracking');
    }
};
