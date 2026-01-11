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

            $table->decimal('price', 8, 2)->default(0);

            // SPEED
            $table->integer('download_speed')->nullable();
            $table->string('download_unit', 10)->nullable(); // Mbps, Kbps

            $table->integer('upload_speed')->nullable();
            $table->string('upload_unit', 10)->nullable();

            // DATA
            $table->enum('data_type', ['limited', 'unlimited'])->default('unlimited');
            $table->integer('data_limit')->nullable();
            $table->string('data_unit', 10)->nullable(); // GB, MB

            // DEVICES
            $table->integer('devices')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
