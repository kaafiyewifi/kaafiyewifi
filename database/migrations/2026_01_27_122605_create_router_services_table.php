<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_router_services_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->index();

            $table->string('service', 20)->index(); // hotspot|pppoe
            $table->boolean('is_enabled')->default(false)->index();
            $table->json('config')->nullable();

            $table->timestamps();

            $table->unique(['router_id', 'service'], 'router_services_router_service_unique');
            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_services');
    }
};
