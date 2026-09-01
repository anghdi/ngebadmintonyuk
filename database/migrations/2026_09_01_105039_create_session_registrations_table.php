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
        Schema::create('session_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('play_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->string('payment_method', 20)->index();
            $table->string('payment_status', 20)->default('unpaid')->index();
            $table->string('attendance_status', 20)->default('listed')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();
            $table->unique(['play_session_id', 'phone']);
            $table->index(['phone', 'attendance_status']);
            $table->index(['user_id', 'attendance_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_registrations');
    }
};
