<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_router_provisions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_provisions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->index();

            $table->string('token_hash', 64)->index(); // sha256 hex
            $table->timestamp('requested_at')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->string('status', 30)->default('generated')->index();
            // generated|ran|success|failed|expired

            $table->string('script_version', 30)->default('v1')->index();
            $table->string('error_code', 60)->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_provisions');
    }
};
