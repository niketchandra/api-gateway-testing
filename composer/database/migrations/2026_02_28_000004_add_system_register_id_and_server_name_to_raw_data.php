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
        Schema::table('raw_data', function (Blueprint $table) {
            $table->unsignedBigInteger('system_register_id')->nullable()->after('user_id');
            $table->string('server_name', 255)->nullable()->after('file_id');
            
            $table->index('system_register_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_data', function (Blueprint $table) {
            $table->dropIndex(['system_register_id']);
            $table->dropColumn(['system_register_id', 'server_name']);
        });
    }
};
