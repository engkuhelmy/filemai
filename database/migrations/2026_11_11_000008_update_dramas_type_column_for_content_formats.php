<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ciri Format Kandungan (Tagging: Drama, Short Film, Filem)
     */
    public function up(): void
    {
        // Ubah jenis kolum 'type' kepada VARCHAR(30) untuk menyokong Drama, Short Film, dan Filem
        DB::statement("ALTER TABLE dramas MODIFY COLUMN type VARCHAR(30) NOT NULL DEFAULT 'drama'");
        
        // Selaraskan data lama
        DB::statement("UPDATE dramas SET type = 'drama' WHERE type = 'series' OR type IS NULL OR type = ''");
        DB::statement("UPDATE dramas SET type = 'film' WHERE type = 'movie'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dramas MODIFY COLUMN type ENUM('series','movie') DEFAULT 'series'");
    }
};
