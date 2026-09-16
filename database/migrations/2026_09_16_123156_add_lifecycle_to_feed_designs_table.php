<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feed_designs', function (Blueprint $table) {
            $table->foreignId('connected_from_id')->nullable()->after('created_by')->constrained('feed_designs')->nullOnDelete();
            $table->timestamp('exported_at')->nullable()->after('thumbnail_path')->index();
            $table->timestamp('published_at')->nullable()->after('exported_at')->index();
        });

        $idsByRow = [];
        $designs = DB::table('feed_designs')
            ->select(['id', 'row_number', 'connection_state', 'exported_assets', 'updated_at'])
            ->orderBy('row_number')
            ->get();

        foreach ($designs as $design) {
            $connectionState = json_decode((string) $design->connection_state, true);
            $exportedAssets = json_decode((string) $design->exported_assets, true);
            $sourceRow = $connectionState['incoming_connection'][0]['source_row'] ?? null;
            $updates = [];

            if ($sourceRow !== null && isset($idsByRow[$sourceRow])) {
                $updates['connected_from_id'] = $idsByRow[$sourceRow];
            }

            if (is_array($exportedAssets) && $exportedAssets !== []) {
                $updates['exported_at'] = $design->updated_at;
            }

            if ($updates !== []) {
                DB::table('feed_designs')->where('id', $design->id)->update($updates);
            }

            $idsByRow[$design->row_number] = $design->id;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feed_designs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('connected_from_id');
            $table->dropColumn(['exported_at', 'published_at']);
        });
    }
};
