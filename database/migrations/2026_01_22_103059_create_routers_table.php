<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();                 // display name
            $table->string('identity')->unique();               // RouterOS System Identity (unique)

            $table->string('mgmt_ip')->nullable();              // management IP (LAN/VPN)
            $table->string('public_ip')->nullable();            // optional

            $table->unsignedInteger('api_port')->default(8728);
            $table->string('api_user')->nullable();             // system_api
            $table->text('api_pass_enc')->nullable();           // encrypted password

            $table->string('status')->default('pending');       // pending|connected|offline|failed
            $table->timestamp('last_seen_at')->nullable();
            $table->text('last_error')->nullable();

            $table->foreignId('location_id')->nullable()
                  ->constrained('locations')
                  ->nullOnDelete();

            // FreeRADIUS (future toggle, keep in same table)
            $table->boolean('radius_enabled')->default(false);
            $table->string('radius_server_ip')->nullable();
            $table->text('radius_secret_enc')->nullable();

            $table->timestamps();

            $table->index(['status']);
            $table->index(['location_id']);
            $table->index(['last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
