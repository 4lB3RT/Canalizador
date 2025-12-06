<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('generated_videos', function (Blueprint $table) {
            $table->string('generated_video_id')->primary();
            $table->string('script_id');
            $table->string('title');
            $table->string('video_local_path')->nullable();
            $table->string('audio_local_path')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->index('script_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_videos');
    }
};
