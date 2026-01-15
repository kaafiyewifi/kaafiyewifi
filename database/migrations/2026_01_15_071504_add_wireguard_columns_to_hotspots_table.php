<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->text('wg_private_key')->nullable()->after('api_pass');
            $table->text('wg_public_key')->nullable()->after('wg_private_key');
            $table->string('radius_secret')->nullable()->after('wg_public_key');
        });
    }

    public function down(): void
    {
        Schema::table('hotspots', function (Blueprint $table) {
            $table->dropColumn([
                'wg_private_key',
                'wg_public_key',
                'radius_secret'
            ]);
        });
    }
};
