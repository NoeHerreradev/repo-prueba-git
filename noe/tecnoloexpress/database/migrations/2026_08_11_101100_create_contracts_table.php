<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('type')->default('venta')->index();

            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('owner_contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('deposit', 15, 2)->nullable();
            $table->decimal('monthly_rent', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('signed_at')->nullable();
            $table->string('status')->default('borrador')->index();

            $table->decimal('commission_percent', 5, 2)->default(3);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('vendedor');
            $table->decimal('percent', 5, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('pendiente')->index();
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('contracts');
    }
};
