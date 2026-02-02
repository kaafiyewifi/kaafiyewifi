<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_router_credentials_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_credentials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->unique();

            $table->string('auth_type', 20)->default('api'); // api|ssh|snmp
            $table->string('username', 120)->nullable();
            $table->text('password_encrypted')->nullable(); // encrypted cast

            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_credentials');
    }
};
