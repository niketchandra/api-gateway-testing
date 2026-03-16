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
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_group', 100)->default('general');
            $table->string('setting_key', 150)->unique();
            $table->longText('setting_value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();

            $table->index(['setting_group', 'setting_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_settings');
    }
};
