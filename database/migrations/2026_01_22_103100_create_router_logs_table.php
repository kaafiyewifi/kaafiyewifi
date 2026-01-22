<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('router_id')
                  ->constrained('routers')
                  ->cascadeOnDelete();

            $table->string('level')->default('info');  // info|warning|error
            $table->string('action');                  // provision.fetch | api.test | hotspot.user.add | disconnect ...
            $table->text('message');
            $table->json('context')->nullable();       // store extra info (mask secrets)

            $table->timestamp('created_at')->useCurrent();

            $table->index(['router_id']);
            $table->index(['level']);
            $table->index(['action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_logs');
    }
};
