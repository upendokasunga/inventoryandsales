<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('country', 100)->default('Tanzania');
            $table->string('contact_person', 200)->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('registration_number', 50)->nullable();
            $table->string('website')->nullable();
            $table->foreignId('customer_group_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('available_credit', 12, 2)->default(0);
            $table->decimal('outstanding_balance', 12, 2)->default(0);
            $table->string('payment_terms', 100)->nullable();
            $table->string('credit_status', 20)->default('good');
            $table->date('credit_hold_at')->nullable();
            $table->text('credit_hold_reason')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('code');
            $table->index('credit_status');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
