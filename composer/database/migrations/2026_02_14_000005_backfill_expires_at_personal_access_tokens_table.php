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
        DB::table('personal_access_tokens')
            ->whereNull('expires_at')
            ->update(['expires_at' => '2099-12-31 23:59:59']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('personal_access_tokens')
            ->where('expires_at', '2099-12-31 23:59:59')
            ->update(['expires_at' => null]);
    }
};
