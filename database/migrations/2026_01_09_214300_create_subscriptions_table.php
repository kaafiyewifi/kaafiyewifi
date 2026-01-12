<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('plan_id')
                  ->constrained('subscription_plans')
                  ->cascadeOnDelete();

            $table->decimal('price', 10, 2);

            // ✅ USE DATETIME (NOT TIMESTAMP)
            $table->dateTime('starts_at');
            $table->dateTime('expires_at');

            $table->enum('status', [
                'active',
                'paused',
                'expired',
                'cancelled'
            ])->default('active');

            $table->boolean('auto_renew')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
