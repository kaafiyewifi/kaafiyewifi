<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {

            if (!Schema::hasColumn('subscriptions','router_ip')) {
                $table->string('router_ip')->nullable();
            }

            if (!Schema::hasColumn('subscriptions','router_username')) {
                $table->string('router_username')->nullable();
            }

            if (!Schema::hasColumn('subscriptions','router_password')) {
                $table->string('router_password')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {

            if (Schema::hasColumn('subscriptions','router_ip')) {
                $table->dropColumn('router_ip');
            }

            if (Schema::hasColumn('subscriptions','router_username')) {
                $table->dropColumn('router_username');
            }

            if (Schema::hasColumn('subscriptions','router_password')) {
                $table->dropColumn('router_password');
            }
        });
    }
};
