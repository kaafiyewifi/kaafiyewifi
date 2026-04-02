<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->decimal('price', 12, 2)->default(0);

            $table->unsignedInteger('base_days')->default(30);

            // Upload
            $table->unsignedInteger('upload_speed')->nullable();
            $table->string('upload_unit', 10)->default('Mbps'); // Kbps | Mbps

            // Download
            $table->unsignedInteger('download_speed')->nullable();
            $table->string('download_unit', 10)->default('Mbps'); // Kbps | Mbps

            $table->string('status', 20)->default('active'); // active | inactive

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};