<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voices', function (Blueprint $table) {
            $table->string('voice_id')->primary();
            $table->string('name');
            $table->string('source_audio_path');
            $table->string('converted_audio_path')->nullable();
            $table->string('platform_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voices');
    }
};
