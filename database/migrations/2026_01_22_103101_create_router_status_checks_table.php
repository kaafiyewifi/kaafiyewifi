<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_status_checks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('router_id')
                  ->constrained('routers')
                  ->cascadeOnDelete();

            $table->string('check_type');            // ping|api
            $table->boolean('is_ok')->default(false);

            $table->unsignedInteger('latency_ms')->nullable();
            $table->text('error')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['router_id', 'created_at']);
            $table->index(['check_type']);
            $table->index(['is_ok']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_status_checks');
    }
};
