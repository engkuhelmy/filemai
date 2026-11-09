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
        Schema::create('episode_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drama_id')->constrained('dramas')->cascadeOnDelete();
            $table->foreignId('episode_id')->constrained('episodes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_id', 100)->nullable()->index();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('device_type', 20)->default('mobile')->index(); // mobile, desktop, tablet
            $table->string('browser', 50)->nullable();
            $table->string('platform', 50)->nullable(); // iOS, Android, Windows, Mac
            $table->boolean('is_vip')->default(false)->index();
            $table->timestamp('viewed_at')->useCurrent()->index();
            $table->timestamps();

            // Composite indexes for fast analytics queries
            $table->index(['drama_id', 'viewed_at']);
            $table->index(['episode_id', 'viewed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episode_views');
    }
};
