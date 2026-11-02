<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates the `episodes` table to add/standardize `video_type` ('storage', 'youtube')
     * and ensure `video_url` & `youtube_video_id` are properly indexed and sized.
     */
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            // Add video_type if it doesn't already exist
            if (!Schema::hasColumn('episodes', 'video_type')) {
                $table->enum('video_type', ['storage', 'youtube'])
                      ->default('storage')
                      ->after('title')
                      ->comment('Pilihan sumber media: fail storage langsung atau pautan YouTube');
            }

            // Ensure video_url exists
            if (!Schema::hasColumn('episodes', 'video_url')) {
                $table->string('video_url', 500)
                      ->nullable()
                      ->after('video_type')
                      ->comment('URL storan terus (MP4/WebM) atau pautan asal video');
            }

            // Ensure youtube_video_id exists
            if (!Schema::hasColumn('episodes', 'youtube_video_id')) {
                $table->string('youtube_video_id', 20)
                      ->nullable()
                      ->after('video_url')
                      ->comment('11-aksara unik YouTube Video ID');
            }
        });

        // Data migration / backfill: if legacy video_source exists, sync into video_type
        if (Schema::hasColumn('episodes', 'video_source')) {
            DB::statement("
                UPDATE episodes 
                SET video_type = CASE 
                    WHEN video_source = 'youtube' THEN 'youtube' 
                    ELSE 'storage' 
                END
                WHERE video_type IS NULL OR video_type = 'storage'
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            if (Schema::hasColumn('episodes', 'video_type')) {
                $table->dropColumn('video_type');
            }
        });
    }
};
