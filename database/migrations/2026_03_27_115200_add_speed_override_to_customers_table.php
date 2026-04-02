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
    Schema::table('customers', function (Blueprint $table) {
        $table->boolean('speed_override_enabled')->default(false);

        $table->integer('download_speed')->nullable();
        $table->string('download_unit')->nullable();

        $table->integer('upload_speed')->nullable();
        $table->string('upload_unit')->nullable();
    });
}

public function down(): void
{
    Schema::table('customers', function (Blueprint $table) {
        $table->dropColumn([
            'speed_override_enabled',
            'download_speed',
            'download_unit',
            'upload_speed',
            'upload_unit',
        ]);
    });
}
};
