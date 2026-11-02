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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drama_id')->constrained('dramas')->onDelete('cascade');
            $table->integer('episode_number');
            $table->string('title');
            $table->enum('video_source', ['youtube', 'direct'])->default('youtube');
            $table->string('youtube_video_id', 20)->nullable();
            $table->string('video_url', 500)->nullable();
            $table->integer('duration')->nullable()->default(60); // Duration in seconds
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_free_preview')->default(true);
            $table->timestamps();

            // Prevent duplicate episode_number within the same drama
            $table->unique(['drama_id', 'episode_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
