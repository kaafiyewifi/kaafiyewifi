<?php

// database/migrations/xxxx_xx_xx_create_router_sessions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('router_sessions', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('router_id')->index();

      $table->string('type', 20)->index(); // hotspot|pppoe
      $table->string('username', 120)->nullable()->index();
      $table->string('ip', 64)->nullable()->index();
      $table->string('mac', 64)->nullable()->index();
      $table->unsignedBigInteger('rx_bytes')->nullable();
      $table->unsignedBigInteger('tx_bytes')->nullable();
      $table->unsignedInteger('uptime_sec')->nullable();

      $table->timestamp('seen_at')->index();
      $table->timestamps();

      $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
    });
  }

  public function down(): void {
    Schema::dropIfExists('router_sessions');
  }
};
