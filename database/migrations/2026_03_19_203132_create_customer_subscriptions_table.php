<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_subscriptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('subscriptions')->cascadeOnDelete();

            $table->unsignedInteger('selected_days')->default(30);
            $table->decimal('calculated_price', 12, 2)->default(0);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->string('status', 20)->default('pending'); // pending|active|expired|suspended|cancelled

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_subscriptions');
    }
};