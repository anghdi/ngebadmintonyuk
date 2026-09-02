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
            $table->dropUnique(['play_session_id', 'phone']);
            $table->string('phone', 20)->nullable()->change();
            $table->unique(['play_session_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_registrations', function (Blueprint $table) {
            $table->dropUnique(['play_session_id', 'user_id']);
            $table->unique(['play_session_id', 'phone']);
        });
    }
};
