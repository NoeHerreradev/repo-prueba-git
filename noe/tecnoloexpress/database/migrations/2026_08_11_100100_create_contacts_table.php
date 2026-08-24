<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('comprador');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('whatsapp')->nullable();
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->default('otro');
            $table->foreignId('assigned_agent_id')->nullable()->constrained('users')->nullOnDelete();

            // Preferencias de búsqueda (compradores / arrendatarios)
            $table->decimal('budget_min', 15, 2)->nullable();
            $table->decimal('budget_max', 15, 2)->nullable();
            $table->string('pref_operation')->nullable();
            $table->string('pref_property_type')->nullable();
            $table->string('pref_city')->nullable();
            $table->unsignedTinyInteger('pref_bedrooms_min')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
