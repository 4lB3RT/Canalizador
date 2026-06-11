<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voices', function (Blueprint $table) {
            $table->float('stability')->default(0.5)->after('platform_id');
            $table->float('similarity_boost')->default(0.75)->after('stability');
            $table->float('style')->default(0)->after('similarity_boost');
            $table->float('speed')->default(1.0)->after('style');
            $table->boolean('use_speaker_boost')->default(true)->after('speed');
        });
    }

    public function down(): void
    {
        Schema::table('voices', function (Blueprint $table) {
            $table->dropColumn(['stability', 'similarity_boost', 'style', 'speed', 'use_speaker_boost']);
        });
    }
};
