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
        Schema::create('play_sessions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('scheduled_at')->index();
            $table->string('venue_name');
            $table->string('court_name');
            $table->unsignedBigInteger('price_per_session');
            $table->string('status', 20)->default('scheduled')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('venue_name');
            $table->string('court_name');
            $table->unsignedBigInteger('price_per_session');
            $table->unsignedSmallInteger('initial_credits')->default(4);
            $table->date('starts_on')->index();
            $table->date('expires_on')->nullable()->index();
            $table->string('status', 20)->default('active')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['user_id', 'venue_name', 'court_name', 'price_per_session'], 'memberships_compatibility_index');
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('play_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('membership_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 30)->index();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['play_session_id', 'user_id']);
        });

        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->unique()->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index();
            $table->smallInteger('quantity');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('memberships');
        Schema::dropIfExists('play_sessions');
    }
};
