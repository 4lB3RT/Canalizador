<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->string('category')->default('VIDEO')->after('url');
            $table->text('description')->nullable()->after('title');
            $table->unsignedInteger('duration')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('youtube_videos', function (Blueprint $table) {
            $table->dropColumn(['category', 'description', 'duration']);
        });
    }
};
