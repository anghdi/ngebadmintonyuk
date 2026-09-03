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
        Schema::table('session_registrations', function (Blueprint $table) {
            $table->foreignId('income_id')->nullable()->unique()->after('checked_at')->constrained()->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_registrations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('income_id');
        });
    }
};
