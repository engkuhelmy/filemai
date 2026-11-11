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
        Schema::table('episodes', function (Blueprint $table) {
            if (!Schema::hasColumn('episodes', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('episodes', 'thumbnail_url')) {
                $table->string('thumbnail_url', 500)->nullable()->after('video_url');
            }
            if (!Schema::hasColumn('episodes', 'status')) {
                $table->enum('status', ['draft', 'scheduled', 'published'])->default('published')->after('views_count');
            }
            if (!Schema::hasColumn('episodes', 'scheduled_at')) {
                $table->timestamp('scheduled_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            if (Schema::hasColumn('episodes', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('episodes', 'thumbnail_url')) {
                $table->dropColumn('thumbnail_url');
            }
            if (Schema::hasColumn('episodes', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('episodes', 'scheduled_at')) {
                $table->dropColumn('scheduled_at');
            }
        });
    }
};

