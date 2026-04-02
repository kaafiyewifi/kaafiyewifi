<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();

            $table->string('type', 20); // hotspot | pppoe
            $table->string('full_name');
            $table->string('phone')->unique();

            $table->string('username')->unique();
            $table->string('password')->default('123456');

            $table->unsignedInteger('device_limit')->default(1);

            $table->string('status', 20)->default('active'); // active | inactive | suspended

            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};