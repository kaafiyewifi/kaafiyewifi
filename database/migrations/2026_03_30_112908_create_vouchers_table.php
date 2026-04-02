<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::connection('radius')->create('vouchers', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->integer('duration'); // minutes
        $table->string('type')->default('time');
        $table->boolean('used')->default(false);
        $table->timestamp('used_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
