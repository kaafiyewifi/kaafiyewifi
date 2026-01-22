<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_provision_tokens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('router_id')
                  ->constrained('routers')
                  ->cascadeOnDelete();

            // store HASH only (never store raw token)
            $table->string('token_hash', 64)->unique();

            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();

            $table->timestamps();

            $table->index(['router_id']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_provision_tokens');
    }
};
