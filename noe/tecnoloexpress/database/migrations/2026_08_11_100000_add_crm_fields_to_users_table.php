<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('agente')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->decimal('commission_percent', 5, 2)->default(0)->after('avatar_path');
            $table->boolean('active')->default(true)->after('commission_percent');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'avatar_path', 'commission_percent', 'active']);
        });
    }
};
