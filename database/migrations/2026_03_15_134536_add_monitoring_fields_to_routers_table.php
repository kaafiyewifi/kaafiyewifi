<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {

            $table->string('wg_ip', 45)->nullable()->after('mgmt_host');

            $table->string('board_name')->nullable()->after('wg_ip');
            $table->string('architecture_name')->nullable()->after('board_name');
            $table->string('routeros_version')->nullable()->after('architecture_name');

            $table->unsignedTinyInteger('cpu_load')->nullable()->after('routeros_version');

            $table->unsignedBigInteger('free_memory')->nullable()->after('cpu_load');
            $table->unsignedBigInteger('total_memory')->nullable()->after('free_memory');

            $table->unsignedBigInteger('free_hdd_space')->nullable()->after('total_memory');
            $table->unsignedBigInteger('total_hdd_space')->nullable()->after('free_hdd_space');

            $table->timestamp('provisioned_at')->nullable()->after('last_seen_at');

        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {

            $table->dropColumn([
                'wg_ip',
                'board_name',
                'architecture_name',
                'routeros_version',
                'cpu_load',
                'free_memory',
                'total_memory',
                'free_hdd_space',
                'total_hdd_space',
                'provisioned_at'
            ]);

        });
    }
};