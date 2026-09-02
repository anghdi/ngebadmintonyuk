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
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->string('driver', 20)->default('fcm')->after('user_id')->index();
            $table->text('endpoint')->nullable()->after('installation_id');
            $table->text('public_key')->nullable()->after('endpoint');
            $table->text('auth_token')->nullable()->after('public_key');
            $table->string('content_encoding', 20)->nullable()->after('auth_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('push_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['driver']);
            $table->dropColumn(['driver', 'endpoint', 'public_key', 'auth_token', 'content_encoding']);
        });
    }
};
