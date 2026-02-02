<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            if (!Schema::hasColumn('routers', 'provision_token')) {
                $table->string('provision_token', 128)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('routers', function (Blueprint $table) {
            if (Schema::hasColumn('routers', 'provision_token')) {
                $table->dropUnique(['provision_token']);
                $table->dropColumn('provision_token');
            }
        });
    }
};
