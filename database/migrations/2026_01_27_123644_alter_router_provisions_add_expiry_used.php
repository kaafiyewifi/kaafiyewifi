<?php

// database/migrations/xxxx_xx_xx_xxxxxx_alter_router_provisions_add_expiry_used.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('router_provisions', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('router_provisions', function (Blueprint $table) {
            $table->dropColumn(['expires_at','used_at']);
        });
    }
};