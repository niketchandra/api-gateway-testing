<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE `personal_access_tokens` MODIFY `expires_at` DATETIME NULL DEFAULT '2099-12-31 23:59:59'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "ALTER TABLE `personal_access_tokens` MODIFY `expires_at` DATETIME NULL DEFAULT NULL"
        );
    }
};
