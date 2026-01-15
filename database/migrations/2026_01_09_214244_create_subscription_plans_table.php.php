<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->text('description')->nullable();

            $table->decimal('price', 8, 2)->default(0);

            // SPEED
            $table->integer('download_speed')->nullable();
            $table->string('download_unit', 10)->default('Mbps');

            $table->integer('upload_speed')->nullable();
            $table->string('upload_unit', 10)->default('Mbps');

            // DATA
            $table->enum('data_type', ['limited', 'unlimited'])->default('unlimited');
            $table->integer('data_limit')->nullable();
            $table->string('data_unit', 10)->default('GB');

            // DEVICES
            $table->integer('devices')->default(1);

            // ⏳ SUBSCRIPTION DURATION
            $table->integer('duration_days')->default(30);

            // 🔌 MIKROTIK PROFILE
            $table->string('router_profile')->nullable();

            // STATUS
            $table->boolean('status')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
