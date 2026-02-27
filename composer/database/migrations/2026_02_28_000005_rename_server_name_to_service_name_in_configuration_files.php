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
        Schema::table('configuration_files', function (Blueprint $table) {
            $table->renameColumn('server_name', 'service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuration_files', function (Blueprint $table) {
            $table->renameColumn('service_name', 'server_name');
        });
    }
};
