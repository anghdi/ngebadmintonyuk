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
        Schema::create('feed_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->string('format', 24);
            $table->string('content_type', 24);
            $table->string('headline', 90);
            $table->string('supporting_text', 180)->nullable();
            $table->date('event_date')->nullable();
            $table->string('event_time', 5)->nullable();
            $table->string('venue')->nullable();
            $table->string('price', 30)->nullable();
            $table->string('cta', 40)->nullable();
            $table->string('layout_variant', 24)->default('editorial');
            $table->string('photo_path')->nullable();
            $table->json('layout_settings')->nullable();
            $table->unsignedInteger('row_number')->unique();
            $table->unsignedInteger('grid_position')->default(0);
            $table->boolean('is_seed')->default(false);
            $table->json('connection_state')->nullable();
            $table->json('exported_assets')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->timestamps();

            $table->index(['created_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feed_designs');
    }
};
