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
        DB::table('users')->where('role', 'member')->update(['playing_level' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The previous self-reported levels cannot be reconstructed after reset.
    }
};
