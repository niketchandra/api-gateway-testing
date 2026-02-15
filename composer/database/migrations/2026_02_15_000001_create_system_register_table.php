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
        Schema::create('system_register', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pat_token_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('org_id')->nullable();
            $table->string('system_name', 255);
            $table->string('os_type', 100);
            $table->string('ip_address', 45);
            $table->string('tags', 512)->nullable();
            $table->longText('metadata')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_register');
    }
};
