<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            // WireGuard IP (used for RADIUS + CoA)
            if (!Schema::hasColumn('routers', 'wg_ip')) {
                $table->string('wg_ip', 45)->nullable()->after('mgmt_host')->index();
            }

            // RADIUS secret (must match MikroTik)
            if (!Schema::hasColumn('routers', 'radius_secret')) {
                $table->string('radius_secret', 255)->nullable()->after('wg_ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            if (Schema::hasColumn('routers', 'radius_secret')) {
                $table->dropColumn('radius_secret');
            }

            if (Schema::hasColumn('routers', 'wg_ip')) {
                $table->dropColumn('wg_ip');
            }
        });
    }
};