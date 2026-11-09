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
        if (Schema::hasTable('episodes') && !Schema::hasColumn('episodes', 'is_coming_soon')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->boolean('is_coming_soon')->default(false)->after('is_free_preview');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('episodes') && Schema::hasColumn('episodes', 'is_coming_soon')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropColumn('is_coming_soon');
            });
        }
    }
};
