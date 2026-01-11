<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('subscription_plans', function (Blueprint $table) {

        if (!Schema::hasColumn('subscription_plans','download_speed')) {
            $table->integer('download_speed')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','download_unit')) {
            $table->string('download_unit')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','upload_speed')) {
            $table->integer('upload_speed')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','upload_unit')) {
            $table->string('upload_unit')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','data_type')) {
            $table->string('data_type')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','data_limit')) {
            $table->integer('data_limit')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','data_unit')) {
            $table->string('data_unit')->nullable();
        }

        if (!Schema::hasColumn('subscription_plans','devices')) {
            $table->integer('devices')->nullable();
        }

    });
}



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            //
        });
    }
};
