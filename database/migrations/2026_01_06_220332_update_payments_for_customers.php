<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {

            // Customer link
            if (!Schema::hasColumn('payments', 'customer_id')) {
                $table->foreignId('customer_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained()
                      ->nullOnDelete();
            }

            // Subscription link (FK later)
            if (!Schema::hasColumn('payments', 'subscription_id')) {
                $table->unsignedBigInteger('subscription_id')
                      ->nullable()
                      ->after('customer_id');
            }

            // Paid timestamp
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'subscription_id', 'paid_at']);
        });
    }
};
