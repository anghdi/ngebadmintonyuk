<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_registrations', function (Blueprint $table): void {
            $table->foreignId('guest_id')->nullable()->constrained()->restrictOnDelete();
            $table->unique(['play_session_id', 'guest_id']);
            $table->index(['guest_id', 'attendance_status']);
        });
    }

    public function down(): void
    {
        Schema::table('session_registrations', function (Blueprint $table): void {
            $table->dropUnique(['play_session_id', 'guest_id']);
            $table->dropIndex(['guest_id', 'attendance_status']);
            $table->dropConstrainedForeignId('guest_id');
        });
    }
};
