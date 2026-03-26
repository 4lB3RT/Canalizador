<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_videos', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->string('url');
            $table->timestamp('published_at');
            $table->string('local_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->json('transcription')->nullable();
            $table->json('published_short_ids')->nullable();
            $table->string('channel_id')->nullable();
            $table->timestamps();

            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('set null');
            $table->index('channel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_videos');
    }
};
