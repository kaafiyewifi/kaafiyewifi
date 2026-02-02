<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_routers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('routers', function (Blueprint $table) {
            $table->bigIncrements('id');

            // Future-proof multi-tenant (nullable for now)
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            $table->string('name', 120);
            $table->string('identity', 120); // System -> Identity
            $table->string('mgmt_host', 255)->nullable(); // IP or hostname
            $table->unsignedSmallInteger('api_port')->default(8728);
            $table->boolean('use_tls')->default(false);

            $table->string('status', 30)->default('pending')->index(); 
            // pending|provisioning|connected|offline|error|disabled

            $table->timestamp('last_seen_at')->nullable()->index();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['tenant_id', 'identity'], 'routers_tenant_identity_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routers');
    }
};
