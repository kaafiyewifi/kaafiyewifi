<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('router_services', function (Blueprint $table) {
            $table->id();

            $table->foreignId('router_id')
                  ->constrained('routers')
                  ->cascadeOnDelete();

            $table->boolean('hotspot_enabled')->default(false);
            $table->boolean('pppoe_enabled')->default(false);
            $table->boolean('anti_sharing_enabled')->default(false);

            $table->string('bridge_name')->default('hotspot-bridge');

            // Ports user selected during setup (ether2, ether3...)
            $table->json('selected_ports')->nullable();

            // Basic LAN/DHCP settings (optional; can be configured later)
            $table->string('lan_ip')->nullable();          // e.g. 10.10.0.1/24
            $table->boolean('dhcp_enabled')->default(true);
            $table->string('dhcp_pool')->nullable();       // e.g. 10.10.0.10-10.10.0.250

            $table->timestamp('configured_at')->nullable();

            $table->timestamps();

            $table->unique('router_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('router_services');
    }
};
