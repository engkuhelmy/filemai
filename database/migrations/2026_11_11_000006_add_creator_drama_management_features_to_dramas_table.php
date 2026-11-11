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
        Schema::table('dramas', function (Blueprint $table) {
            // Ciri 2: SEO & Keywords
            if (!Schema::hasColumn('dramas', 'meta_title')) {
                $table->string('meta_title', 255)->nullable()->after('synopsis');
            }
            if (!Schema::hasColumn('dramas', 'meta_description')) {
                $table->text('meta_description')->nullable()->after('meta_title');
            }
            if (!Schema::hasColumn('dramas', 'keywords')) {
                $table->text('keywords')->nullable()->after('meta_description');
            }

            // Ciri 3: Creator Info & Bio
            if (!Schema::hasColumn('dramas', 'creator_bio')) {
                $table->text('creator_bio')->nullable()->after('keywords');
            }

            // Ciri 6: Multi-genre tags
            if (!Schema::hasColumn('dramas', 'genres')) {
                $table->text('genres')->nullable()->after('genre');
            }

            // Ciri 7: Series Release Status
            if (!Schema::hasColumn('dramas', 'release_status')) {
                $table->enum('release_status', ['ongoing', 'completed', 'hiatus', 'upcoming'])->default('ongoing')->after('type');
            }

            // Ciri 8: Trailer URL
            if (!Schema::hasColumn('dramas', 'trailer_url')) {
                $table->string('trailer_url', 500)->nullable()->after('banner_url');
            }

            // Ciri 9: Cast & Crew
            if (!Schema::hasColumn('dramas', 'cast')) {
                $table->text('cast')->nullable()->after('release_status');
            }

            // Ciri 10: Age Rating & Content Warnings
            if (!Schema::hasColumn('dramas', 'age_rating')) {
                $table->string('age_rating', 20)->default('13+')->after('cast');
            }
            if (!Schema::hasColumn('dramas', 'content_warnings')) {
                $table->text('content_warnings')->nullable()->after('age_rating');
            }

            // Ciri 11: Creator Notice Board
            if (!Schema::hasColumn('dramas', 'announcement')) {
                $table->text('announcement')->nullable()->after('content_warnings');
            }

            // Ciri 12: Bundle/Season Pricing
            if (!Schema::hasColumn('dramas', 'season_price')) {
                $table->decimal('season_price', 8, 2)->nullable()->after('announcement');
            }
            if (!Schema::hasColumn('dramas', 'is_bundle_enabled')) {
                $table->boolean('is_bundle_enabled')->default(false)->after('season_price');
            }
            if (!Schema::hasColumn('dramas', 'bundle_discount_note')) {
                $table->string('bundle_discount_note', 255)->nullable()->after('is_bundle_enabled');
            }

            // Ciri 13: Social Media Links & Official Hashtag
            if (!Schema::hasColumn('dramas', 'social_hashtag')) {
                $table->string('social_hashtag', 100)->nullable()->after('bundle_discount_note');
            }
            if (!Schema::hasColumn('dramas', 'social_links')) {
                $table->text('social_links')->nullable()->after('social_hashtag');
            }

            // Ciri 14: Theme Accent Color
            if (!Schema::hasColumn('dramas', 'accent_color')) {
                $table->string('accent_color', 30)->default('#E50914')->after('social_links');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dramas', function (Blueprint $table) {
            $table->dropColumn([
                'meta_title', 'meta_description', 'keywords',
                'creator_bio', 'genres', 'release_status',
                'trailer_url', 'cast', 'age_rating',
                'content_warnings', 'announcement', 'season_price',
                'is_bundle_enabled', 'bundle_discount_note',
                'social_hashtag', 'social_links', 'accent_color'
            ]);
        });
    }
};

