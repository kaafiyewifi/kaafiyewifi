<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {

            // duration type: days / hours
            $table->enum('duration_type', ['days', 'hours'])
                  ->default('days')
                  ->after('price');

            // base duration (e.g 30 days / 24 hours)
            $table->integer('base_duration')
                  ->default(30)
                  ->after('duration_type');

        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['duration_type', 'base_duration']);
        });
    }
};
