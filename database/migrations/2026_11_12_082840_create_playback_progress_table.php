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
        Schema::create('playback_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('session_id', 100)->nullable()->index();
            $table->foreignId('drama_id')->constrained('dramas')->onDelete('cascade');
            $table->foreignId('episode_id')->constrained('episodes')->onDelete('cascade');
            $table->unsignedInteger('progress_seconds')->default(0);
            $table->unsignedInteger('duration_seconds')->default(60);
            $table->boolean('completed')->default(false);
            $table->timestamp('last_watched_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'episode_id']);
            $table->index(['session_id', 'episode_id']);
            $table->index(['user_id', 'drama_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('playback_progress');
    }
};
