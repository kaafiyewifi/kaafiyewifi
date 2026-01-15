<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('hotspots', function (Blueprint $table) {

            $table->id();

            $table->foreignId('location_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('title');
            $table->string('ssid');

            $table->integer('download_speed')->nullable();
            $table->integer('upload_speed')->nullable();
            $table->string('speed_unit')->default('Mbps');

            $table->string('nas_type')->default('MikroTik');
            $table->string('physical_address')->nullable();

            // Router info
            $table->unsignedBigInteger('router_id')->nullable();
            $table->string('router_ip')->nullable()->index();
            $table->integer('api_port')->default(8728);
            $table->string('api_user')->nullable();
            $table->text('api_pass')->nullable();

            // WireGuard VPN
            $table->text('wg_private_key')->nullable();
            $table->text('wg_public_key')->nullable();

            // Radius
            $table->string('radius_secret')->nullable();

            $table->enum('status',['active','inactive'])->default('active');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotspots');
    }
};
