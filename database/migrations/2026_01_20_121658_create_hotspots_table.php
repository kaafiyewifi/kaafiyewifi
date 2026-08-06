<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('hotspots', function (Blueprint $table) {
      $table->id();

      $table->foreignId('location_id')->constrained()->cascadeOnDelete();

      $table->string('ssid_name');
      $table->string('physical_address')->nullable();

      $table->enum('nat_type', ['mikrotik','cambium','tanaza'])->default('mikrotik');
      $table->enum('setup_type', ['simple','advanced'])->default('simple');

      $table->enum('setup_profile', [
        'full_wg',        // Full Setup VPN (WireGuard)
        'full_ovpn',      // Full setup (with OpenVPN)
        'vpn_wg',         // Setup VPN (WireGuard)
        'vpn_ovpn'        // Setup VPN (OpenVPN)
      ])->default('full_wg');

      $table->enum('status', ['active','inactive'])->default('active');

      // Monitoring
      $table->timestamp('last_seen_at')->nullable();
      $table->unsignedInteger('active_users')->default(0);
      $table->string('vpn_ip')->nullable();
      $table->string('reported_ip')->nullable();

      // Router auth
      $table->string('token', 80)->unique();

      // Script snapshot (one-time generate)
      $table->timestamp('script_generated_at')->nullable();
      $table->unsignedInteger('script_version')->default(1);
      $table->longText('script_snapshot')->nullable();

      $table->timestamps();

      $table->index(['location_id', 'status']);
      $table->index(['last_seen_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('hotspots');
  }
};
