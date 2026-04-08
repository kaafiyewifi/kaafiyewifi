<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('location_id');
                $table->index('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('subscriptions', 'created_by')) {
                $table->dropIndex(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};