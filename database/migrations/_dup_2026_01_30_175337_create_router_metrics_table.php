<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_metrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->index();

            $table->unsignedTinyInteger('cpu_load')->nullable(); // 0-100
            $table->unsignedBigInteger('free_memory')->nullable(); // bytes
            $table->unsignedBigInteger('total_memory')->nullable(); // bytes
            $table->unsignedBigInteger('free_hdd_space')->nullable(); // bytes
            $table->unsignedBigInteger('total_hdd_space')->nullable(); // bytes

            $table->string('uptime', 50)->nullable();
            $table->string('version', 50)->nullable();
            $table->string('board_name', 80)->nullable();
            $table->string('architecture_name', 80)->nullable();

            $table->timestamp('collected_at')->useCurrent()->index();
            $table->timestamps();

            $table->foreign('router_id')
                ->references('id')->on('routers')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_metrics');
    }
};
