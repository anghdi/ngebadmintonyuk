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
        Schema::create('shuttlecock_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->unsignedSmallInteger('pieces_per_tube')->default(12);
            $table->unsignedInteger('minimum_stock')->default(12);
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['name', 'brand']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shuttlecock_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('play_session_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->index();
            $table->integer('quantity');
            $table->unsignedBigInteger('unit_cost')->nullable();
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
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('shuttlecock_items');
    }
};
