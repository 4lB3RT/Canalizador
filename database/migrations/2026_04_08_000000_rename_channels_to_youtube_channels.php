<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
        });

        Schema::rename('channels', 'youtube_channels');

        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('youtube_channels')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->dropForeign(['channel_id']);
        });

        Schema::rename('youtube_channels', 'channels');

        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('set null');
        });
    }
};
