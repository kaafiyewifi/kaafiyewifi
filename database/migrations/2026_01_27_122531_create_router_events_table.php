<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_router_events_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->index();

            $table->string('type', 80)->index();   // connected, api_failed, snmp_ok...
            $table->json('payload')->nullable();   // flexible details
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_events');
    }
};
