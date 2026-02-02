<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('router_id')->index();
            $table->string('type', 50)->index(); // provisioning|heartbeat|error|info
            $table->boolean('success')->default(true)->index();
            $table->string('message', 255);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('router_id')->references('id')->on('routers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_logs');
    }
};
