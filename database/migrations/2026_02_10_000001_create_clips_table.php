<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clips', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('video_id');
            $table->tinyInteger('sequence');
            $table->string('generation_id');
            $table->string('status')->default('generating');
            $table->string('local_path')->nullable();
            $table->string('video_uri', 1024)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();

            $table->unique(['video_id', 'sequence']);
            $table->index('video_id');

            $table->foreign('video_id')
                ->references('id')
                ->on('videos')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clips');
    }
};
