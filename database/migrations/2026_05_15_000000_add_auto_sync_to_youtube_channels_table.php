<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->boolean('auto_sync')->default(false)->after('channel_brand');
            $table->index(['user_id', 'auto_sync']);
        });
    }

    public function down(): void
    {
        Schema::table('youtube_channels', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'auto_sync']);
            $table->dropColumn('auto_sync');
        });
    }
};
