<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Support extended drama statuses (published, draft, archived, pending)
     * so that 'Tamat (Completed)' dramas can be auto-hidden as 'draft'.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE dramas MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'published'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE dramas MODIFY COLUMN status ENUM('pending','published','rejected') DEFAULT 'pending'");
    }
};

