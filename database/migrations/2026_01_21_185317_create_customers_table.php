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

            $table->foreignId('location_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('full_name');

            // phone = main identifier
            $table->string('phone')->unique();

            // username = phone (auto)
            $table->string('username')->unique();

            // default password = 123456 (model handles it)
            $table->string('password');

            // readable status
            $table->enum('status', ['active', 'inactive'])
                  ->default('active');

            // fast boolean flag (tests + UI)
            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
