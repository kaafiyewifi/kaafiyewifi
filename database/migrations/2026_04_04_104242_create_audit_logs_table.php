<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->id();

        $table->unsignedBigInteger('user_id')->nullable();
        $table->string('action')->nullable();
        $table->string('target_type')->nullable();
        $table->unsignedBigInteger('target_id')->nullable();

        $table->text('description')->nullable();
        $table->string('ip_address')->nullable();

        $table->json('properties')->nullable();

        $table->timestamps();

        $table->index(['user_id']);
        $table->index(['action']);
    });
}
};
