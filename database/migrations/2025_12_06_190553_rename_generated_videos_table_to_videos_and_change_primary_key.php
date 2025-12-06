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
        // Rename the table first
        Schema::rename('generated_videos', 'videos');

        // Rename the primary key column using raw SQL for better compatibility
        // MySQL/MariaDB syntax
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE videos CHANGE generated_video_id id VARCHAR(255) NOT NULL');
        } else {
            // PostgreSQL syntax
            DB::statement('ALTER TABLE videos RENAME COLUMN generated_video_id TO id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the primary key column back
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE videos CHANGE id generated_video_id VARCHAR(255) NOT NULL');
        } else {
            // PostgreSQL syntax
            DB::statement('ALTER TABLE videos RENAME COLUMN id TO generated_video_id');
        }

        // Rename the table back
        Schema::rename('videos', 'generated_videos');
    }
};
