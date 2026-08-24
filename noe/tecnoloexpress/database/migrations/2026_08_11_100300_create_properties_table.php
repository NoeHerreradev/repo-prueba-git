<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            $table->string('operation')->default('venta')->index();
            $table->string('type')->default('apartamento')->index();
            $table->string('status')->default('disponible')->index();

            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('rent_price', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('maintenance_fee', 12, 2)->nullable();

            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->unsignedTinyInteger('parking_spaces')->nullable();
            $table->decimal('area_built', 10, 2)->nullable();
            $table->decimal('area_lot', 10, 2)->nullable();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->string('floor')->nullable();

            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('show_exact_address')->default(false);

            $table->foreignId('owner_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('commission_percent', 5, 2)->default(3);
            $table->boolean('exclusive')->default(false);
            $table->date('captured_at')->nullable();

            $table->boolean('published')->default(false)->index();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
